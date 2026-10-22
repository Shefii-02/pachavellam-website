<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\ExamCategory;
use App\Models\ExamCourse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Public course catalogue for the app and the website (published courses only), for every exam type.
 *   POST api/exam-course-categories   category tree of one exam type (or of every exam type) with the number of courses in each
 *   POST api/exam-courses             course list; filter by exam, category (sub categories included), type, level, language, search
 *   POST api/exam-course-single       one course
 * The exam is given as  "exam": "kpsc"  (its slug) or its id. The exam types themselves come from GET api/exams.
 */
class ExamCourseController extends Controller
{
    /** Category tree for the filter. Categories without a published course are left out unless all=1. */
    public function categories(Request $request)
    {
        $request->validate(['exam' => 'nullable']);

        if ($request->filled('exam')) {
            $exam = $this->resolveExam($request->exam);

            return $exam
                ? response()->json(['exam' => $this->examSummary($exam), 'data' => $this->tree($exam, $request->boolean('all')), 'status' => 200])
                : response()->json(['message' => 'Exam not found.', 'status' => 404], 404);
        }

        // no exam given: every exam type with its own tree
        $data = CompetitiveExam::active()->get()->map(fn ($e) => $this->examSummary($e) + ['categories' => $this->tree($e, $request->boolean('all'))])->values();

        return response()->json(['data' => $data, 'status' => 200]);
    }

    public function index(Request $request)
    {
        $request->validate([
            'exam' => 'nullable',
            'category_id' => 'nullable|integer',
            'q' => 'nullable|string|max:100',
            'type' => 'nullable|in:free,paid',
            'level' => 'nullable|in:beginner,intermediate,advanced,all',
            'language' => 'nullable|in:ml,en,both',
            'per_page' => 'nullable|integer|min:1|max:50',
        ]);

        $exam = null;
        if ($request->filled('exam')) {
            $exam = $this->resolveExam($request->exam);
            if (!$exam) {
                return response()->json(['message' => 'Exam not found.', 'status' => 404], 404);
            }
        }

        $query = ExamCourse::where('status', 'published')->where('slug', 'not like', 'series-%')
            ->whereIn('exam_id', CompetitiveExam::where('status', 1)->select('id'))
            ->when($exam, fn ($q) => $q->where('exam_id', $exam->id))
            // a category filter only matches categories of the exam being asked for
            ->when($request->filled('category_id'), fn ($q) => $q->inCategory($request->category_id))
            ->when($request->filled('q'), fn ($q) => $q->where(fn ($w) => $w->where('title', 'like', '%' . $request->q . '%')->orWhere('short_description', 'like', '%' . $request->q . '%')))
            ->when($request->filled('type'), fn ($q) => $q->where('course_type', $request->type))
            ->when($request->filled('level'), fn ($q) => $q->whereIn('level', [$request->level, 'all']))
            ->when($request->filled('language'), fn ($q) => $q->whereIn('language', [$request->language, 'both']))
            ->when($request->boolean('featured'), fn ($q) => $q->where('is_featured', 1))
            ->with(['exam:id,slug,name,short_name,color', 'categories:id,name,slug', 'features:id,slug,name,image', 'members' => fn ($m) => $m->wherePivot('member_role', 'teacher')->select('users.id', 'users.name', 'users.image')])
            ->orderByDesc('is_featured')->orderBy('position')->orderByDesc('id');

        $page = $query->paginate($request->integer('per_page', 20));

        return response()->json([
            'data' => $page->getCollection()->map(fn ($c) => $this->summary($c))->values(),
            'meta' => ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'per_page' => $page->perPage(), 'total' => $page->total()],
            'status' => 200,
        ]);
    }

    public function single(Request $request)
    {
        $request->validate(['id' => 'required_without:slug|nullable|integer', 'slug' => 'required_without:id|nullable|string']);

        $course = ExamCourse::where('status', 'published')
            ->whereIn('exam_id', CompetitiveExam::where('status', 1)->select('id'))
            ->when($request->filled('id'), fn ($q) => $q->where('id', $request->id), fn ($q) => $q->where('slug', $request->slug))
            ->with(['exam:id,slug,name,short_name,color', 'categories:id,name,slug', 'features', 'members' => fn ($m) => $m->wherePivot('member_role', 'teacher')->select('users.id', 'users.name', 'users.image')])
            ->first();

        if (!$course) {
            return response()->json(['message' => 'Course not found.', 'status' => 404], 404);
        }

        $categoryNames = ExamCategory::where('exam_id', $course->exam_id)->pluck('name', 'id');

        return response()->json([
            'data' => $this->summary($course) + [
                'description' => $course->description,
                'audience' => $course->audience,
                'highlights' => $course->highlights ?? [],
                'requirements' => $course->requirements ?? [],
                'promo_video_url' => $course->promo_video_url,
                'starts_on' => optional($course->starts_on)->toDateString(),
                'ends_on' => optional($course->ends_on)->toDateString(),
                'features_detail' => $course->features->map(fn ($f) => [
                    'slug' => $f->slug,
                    'name' => $f->name,
                    'image' => $f->imageUrl(),
                    'kind' => $f->content_kind,
                    'scope' => $f->pivot->scope,
                    // for "category / subject based" features: which categories (subjects) it covers
                    'categories' => collect(json_decode($f->pivot->category_ids ?? '[]', true) ?: [])->map(fn ($i) => ['id' => $i, 'name' => $categoryNames[$i] ?? null])->values(),
                ])->values(),
            ],
            'status' => 200,
        ]);
    }

    // ------------------------------------------------------------- helpers

    private function resolveExam($value): ?CompetitiveExam
    {
        return CompetitiveExam::where('status', 1)
            ->where(fn ($q) => ctype_digit((string) $value) ? $q->where('id', $value)->orWhere('slug', $value) : $q->where('slug', $value))
            ->first();
    }

    private function examSummary(CompetitiveExam $e): array
    {
        return ['id' => $e->id, 'slug' => $e->slug, 'name' => $e->name, 'short_name' => $e->short_name, 'color' => $e->color];
    }

    /** One exam type's category tree, each node with the number of published courses in it and below it. */
    private function tree(CompetitiveExam $exam, bool $all): array
    {
        $cats = ExamCategory::where('exam_id', $exam->id)->where('status', 1)->orderBy('position')->orderBy('id')
            ->get(['id', 'parent_id', 'name', 'slug', 'image', 'icon', 'position']);

        $direct = DB::table('exam_course_categories as cc')
            ->join('exam_courses as c', 'c.id', '=', 'cc.course_id')
            ->where('c.status', 'published')->where('c.exam_id', $exam->id)
            ->select('cc.category_id', 'cc.course_id')->get()->groupBy('category_id')->map(fn ($g) => $g->pluck('course_id')->all());

        $byParent = $cats->groupBy('parent_id');

        $build = function ($parentId) use (&$build, $byParent, $direct, $all) {
            $nodes = [];
            foreach ($byParent->get($parentId, collect()) as $cat) {
                $children = $build($cat->id);
                // a category counts its own courses and those of its sub categories, each course once
                $ids = collect($direct->get($cat->id, []));
                foreach ($children as $child) {
                    $ids = $ids->merge($child['_course_ids']);
                }
                $ids = $ids->unique()->values();

                if (!$all && $ids->isEmpty()) {
                    continue;
                }

                $nodes[] = [
                    'id' => $cat->id,
                    'name' => $cat->name,
                    'slug' => $cat->slug,
                    'image' => $cat->image ? url('storage/' . $cat->image) : null,
                    'courses_count' => $ids->count(),
                    'children' => array_map(fn ($c) => collect($c)->except('_course_ids')->all(), $children),
                    '_course_ids' => $ids->all(),
                ];
            }

            return $nodes;
        };

        return array_map(fn ($c) => collect($c)->except('_course_ids')->all(), $build(null));
    }

    private function summary(ExamCourse $c): array
    {
        return [
            'id' => $c->id,
            'exam' => $c->exam ? $this->examSummary($c->exam) : null,
            'title' => $c->title,
            'slug' => $c->slug,
            'short_description' => $c->short_description,
            'cover_image' => $c->cover_image ? url('storage/' . $c->cover_image) : null,
            'course_type' => $c->course_type,
            'price' => $c->price,
            'discount_price' => $c->discount_price,
            'effective_price' => $c->effectivePrice(),
            'validity' => $c->validityLabel(),
            'level' => $c->level,
            'language' => $c->language,
            'duration_hours' => $c->duration_hours,
            'certificate' => $c->certificate_enabled,
            'is_featured' => $c->is_featured,
            'tags' => $c->tags ?? [],
            'categories' => $c->categories->map(fn ($x) => ['id' => $x->id, 'name' => $x->name, 'slug' => $x->slug])->values(),
            'features' => $c->features->map(fn ($f) => ['slug' => $f->slug, 'name' => $f->name, 'image' => $f->imageUrl()])->values(),
            'teachers' => $c->members->map(fn ($m) => ['name' => $m->name, 'image' => $m->image ? url('storage/users/' . $m->image) : null])->values(),
        ];
    }
}
