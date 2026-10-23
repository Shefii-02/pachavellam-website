<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Concerns\ResolvesExam;
use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\ExamCategory;
use App\Models\ExamCourse;
use App\Models\ExamFeature;
use App\Models\User;
use App\Services\CourseWorkspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Courses of an exam type: admin/exam/{exam}/courses
 *  - Admin: sees and does everything.
 *  - Staff / teachers added to a course: see it; can edit its details and content when "can edit" is on.
 *  - Only an Admin creates or deletes courses and chooses a course's features and staff.
 */
class CourseController extends Controller
{
    use ResolvesExam;

    public function index(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);
        $user = $request->user();

        $courses = ExamCourse::forExam($exam->id)->visibleTo($user)->where('slug', 'not like', 'series-%')
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->q . '%'))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('type'), fn ($q) => $q->where('course_type', $request->type))
            ->when($request->filled('category'), fn ($q) => $q->inCategory($request->category))
            ->with('categories:id,name')->withCount(['features', 'members', 'nodes'])
            ->orderByDesc('is_featured')->orderBy('position')->orderByDesc('id')
            ->paginate(per_page(20))->withQueryString();

        return view('admin.exam.course.index', [
            'exam' => $exam,
            'layout' => $exam->layout(),
            'base' => $exam->examUrl('courses'),
            'courses' => $courses,
            'categories' => ExamCategory::optionsFor($exam->id),
            'isAdmin' => $user->type === 'Admin',
        ]);
    }

    public function create(Request $request, $examSlug)
    {
        $this->adminOnly($request);

        return view('admin.exam.course.form', $this->formData($this->exam($examSlug), null));
    }

    public function store(Request $request, $examSlug)
    {
        $this->adminOnly($request);
        $exam = $this->exam($examSlug);

        $data = $this->validated($request, $exam);
        $data['exam_id'] = $exam->id;
        $data['slug'] = $this->uniqueSlug($data['title']);
        $data['created_by'] = $request->user()->id;

        if ($request->hasFile('cover_image')) {
            $data['cover_image'] = $request->file('cover_image')->store('exam-courses', 'public');
        }

        $course = DB::transaction(function () use ($data, $request, $exam) {
            $course = ExamCourse::create($data);
            $this->syncCategories($course, $request, $exam);
            $this->syncFeatures($course, $request, $exam);
            $this->syncMembers($course, $request);

            return $course;
        });

        return redirect($exam->examUrl('courses/' . $course->id))->with('message', 'Course created. Add its content below.');
    }

    public function show(Request $request, $examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $course = $this->course($exam, $id);
        abort_unless($course->canBeViewedBy($request->user()), 403, 'You do not have access to this course.');

        $members = $course->members()->orderBy('name')->get();
        $workspace = CourseWorkspace::data($course, $request->user());

        return view('admin.exam.course.show', $workspace + [
            'base' => $exam->examUrl('courses'),
            'categories' => $course->categories()->get(),
            'categoryNames' => ExamCategory::where('exam_id', $exam->id)->pluck('name', 'id'),
            'members' => $members,
            'checklist' => $this->checklist($course, $workspace['navFeatures'], $members),
        ]);
    }

    /** What is still missing before the course is ready to publish. */
    private function checklist(ExamCourse $course, $nav, $members): array
    {
        $filled = $nav->sum('count');

        return [
            ['Cover image', (bool) $course->cover_image, 'Students see it on the course card.'],
            ['Description', mb_strlen(trim((string) $course->description)) >= 50, 'Write at least a few lines about the course.'],
            ['Category', $course->categories()->exists(), 'Needed so students can find it with the category filter.'],
            ['Price', $course->isFree() || $course->price > 0, 'A paid course needs a price.'],
            ['Features', $nav->isNotEmpty(), 'Tick what the course includes.'],
            ['Content or exams added', $filled > 0, 'Add files, articles, exams or related items.'],
            ['A teacher or staff member', $members->isNotEmpty(), 'Someone who looks after the course.'],
        ];
    }

    public function edit(Request $request, $examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $course = $this->course($exam, $id);
        $this->authorizeEdit($request, $course);

        return view('admin.exam.course.form', array_merge($this->formData($exam, $course), ['isAdmin' => $request->user()->type === 'Admin']));
    }

    public function update(Request $request, $examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $course = $this->course($exam, $id);
        $this->authorizeEdit($request, $course);
        $isAdmin = $request->user()->type === 'Admin';

        $data = $this->validated($request, $exam);

        if ($request->hasFile('cover_image')) {
            $this->deleteFile($course->cover_image);
            $data['cover_image'] = $request->file('cover_image')->store('exam-courses', 'public');
        } elseif ($request->boolean('remove_cover')) {
            $this->deleteFile($course->cover_image);
            $data['cover_image'] = null;
        }

        DB::transaction(function () use ($course, $data, $request, $isAdmin, $exam) {
            $course->update($data);
            $this->syncCategories($course, $request, $exam);

            // features and the people with access are an Admin decision
            if ($isAdmin) {
                $this->syncFeatures($course, $request, $exam);
                $this->syncMembers($course, $request);
            }
        });

        return redirect($exam->examUrl('courses/' . $course->id))->with('message', 'Course updated');
    }

    public function destroy(Request $request, $examSlug, $id)
    {
        $this->adminOnly($request);
        $exam = $this->exam($examSlug);
        $course = $this->course($exam, $id);

        $course->delete();   // goes to Trash with its content and files; Trash removes them for good

        return redirect($exam->examUrl('courses'))->with('message', 'Course moved to Trash');
    }

    public function togglePublish(Request $request, $examSlug, $id)
    {
        $exam = $this->exam($examSlug);
        $course = $this->course($exam, $id);
        $this->authorizeEdit($request, $course);

        $course->status = $course->status === 'published' ? 'draft' : 'published';
        $course->save();

        return back()->with('message', $course->status === 'published' ? 'Course published' : 'Course moved to draft');
    }

    // ------------------------------------------------------------- helpers

    private function course(CompetitiveExam $exam, $id): ExamCourse
    {
        return ExamCourse::forExam($exam->id)->findOrFail($id);
    }

    private function formData(CompetitiveExam $exam, ?ExamCourse $course): array
    {
        // only the features offered for this exam type
        $features = ExamFeature::active()->forExam($exam->id)->get();
        $selected = $course ? $course->features()->get()->keyBy('id') : collect();

        // everyone who can be given access: Admin Staff, with their Spatie role to tell teachers from staff
        $people = User::where('type', 'Admin Staff')->orderBy('name')->get(['id', 'name', 'email']);
        $teacherIds = DB::table('model_has_roles')->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('roles.name', 'Teacher')->where('model_has_roles.model_type', User::class)->pluck('model_id')->all();

        return [
            'exam' => $exam,
            'layout' => $exam->layout(),
            'base' => $exam->examUrl('courses'),
            'course' => $course,
            'features' => $features,
            'selected' => $selected,
            'categories' => ExamCategory::optionsFor($exam->id),
            'selectedCategories' => $course ? $course->categories()->pluck('exam_categories.id')->all() : [],
            'people' => $people,
            'teacherIds' => $teacherIds,
            'assigned' => $course ? $course->members()->get()->keyBy('id') : collect(),
            'isAdmin' => true,
        ];
    }

    private function validated(Request $request, CompetitiveExam $exam): array
    {
        $paid = $request->course_type === 'paid';

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string|max:300',
            'description' => 'nullable|string',
            // categories have to belong to this exam type
            'category_ids' => 'nullable|array',
            'category_ids.*' => ['integer', Rule::exists('exam_categories', 'id')->where('exam_id', $exam->id)],
            'level' => ['required', Rule::in(array_keys(ExamCourse::LEVELS))],
            'language' => ['required', Rule::in(array_keys(ExamCourse::LANGUAGES))],
            'course_type' => ['required', Rule::in(['free', 'paid'])],
            'price' => [$paid ? 'required' : 'nullable', 'numeric', 'min:0', 'max:9999999'],
            'discount_price' => ['nullable', 'numeric', 'min:0', $paid ? 'lt:price' : 'nullable'],
            'validity_type' => ['required', Rule::in(['lifetime', 'days', 'date'])],
            'validity_days' => ['required_if:validity_type,days', 'nullable', 'integer', 'min:1', 'max:3650'],
            'valid_until' => ['required_if:validity_type,date', 'nullable', 'date'],
            'starts_on' => 'nullable|date',
            'ends_on' => 'nullable|date|after_or_equal:starts_on',
            'duration_hours' => 'nullable|numeric|min:0|max:9999',
            'highlights' => 'nullable|string',
            'requirements' => 'nullable|string',
            'audience' => 'nullable|string',
            'tags' => 'nullable|string|max:500',
            'promo_video_url' => 'nullable|url|max:500',
            'status' => ['required', Rule::in(array_keys(ExamCourse::STATUSES))],
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'position' => 'nullable|integer|min:0',
            'cover_image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3072',
        ], ['discount_price.lt' => 'The discount price must be lower than the price.']);

        $lines = fn ($text) => collect(preg_split('/\r\n|\r|\n/', (string) $text))->map(fn ($l) => trim($l))->filter()->values()->all();

        $data['highlights'] = $lines($data['highlights'] ?? '');
        $data['requirements'] = $lines($data['requirements'] ?? '');
        $data['tags'] = collect(explode(',', (string) ($data['tags'] ?? '')))->map(fn ($t) => Str::limit(trim($t), 30, ''))->filter()->unique()->take(10)->values()->all();
        $data['short_description'] = $data['short_description'] ?? null;
        $data['description'] = $data['description'] ?? null;
        $data['audience'] = $data['audience'] ?? null;
        $data['promo_video_url'] = $data['promo_video_url'] ?? null;
        $data['meta_title'] = $data['meta_title'] ?? null;
        $data['meta_description'] = $data['meta_description'] ?? null;
        $data['starts_on'] = $data['starts_on'] ?? null;
        $data['ends_on'] = $data['ends_on'] ?? null;
        $data['duration_hours'] = $data['duration_hours'] ?? null;

        // free courses carry no price; unused validity fields are cleared
        if (!$paid) {
            $data['price'] = 0;
            $data['discount_price'] = null;
        }
        $data['price'] = $data['price'] ?? 0;
        $data['discount_price'] = $data['discount_price'] ?? null;
        $data['validity_days'] = $data['validity_type'] === 'days' ? ($data['validity_days'] ?? null) : null;
        $data['valid_until'] = $data['validity_type'] === 'date' ? ($data['valid_until'] ?? null) : null;

        $data['position'] = $data['position'] ?? 0;
        $data['certificate_enabled'] = $request->boolean('certificate_enabled');
        $data['is_featured'] = $request->boolean('is_featured');
        unset($data['cover_image'], $data['category_ids']);

        return $data;
    }

    /** Any number of categories of this exam type; the first one is kept as the main category. */
    private function syncCategories(ExamCourse $course, Request $request, CompetitiveExam $exam): void
    {
        $valid = ExamCategory::where('exam_id', $exam->id)->pluck('id');
        $ids = collect($request->input('category_ids', []))->map(fn ($v) => (int) $v)->unique()->filter(fn ($v) => $valid->contains($v))->values();

        $course->categories()->sync($ids->all());
        $course->forceFill(['category_id' => $ids->first()])->save();
    }

    /** Ticked features, with common / category based scope for exam features. */
    private function syncFeatures(ExamCourse $course, Request $request, CompetitiveExam $exam): void
    {
        $ids = collect($request->input('feature_ids', []))->map(fn ($v) => (int) $v)->unique();
        $features = ExamFeature::forExam($exam->id)->whereIn('id', $ids)->get()->keyBy('id');   // a feature not offered for this exam is ignored
        $validCategories = ExamCategory::where('exam_id', $exam->id)->pluck('id')->all();

        $sync = [];
        foreach ($features as $id => $feature) {
            $scope = 'common';
            $categoryIds = null;

            if ($feature->supports_subjects && $request->input("scope.$id") === 'category') {
                $categoryIds = collect($request->input("categories.$id", []))->map(fn ($v) => (int) $v)
                    ->filter(fn ($v) => in_array($v, $validCategories))->unique()->values()->all();
                if ($categoryIds) {
                    $scope = 'category';
                }
            }

            $sync[$id] = [
                'scope' => $scope,
                'category_ids' => $categoryIds ? json_encode($categoryIds) : null,
                'note' => Str::limit((string) $request->input("note.$id"), 250, ''),
            ];
        }

        // detach unticked features (their content nodes are kept so ticking again brings them back)
        $course->features()->sync($sync);
    }

    /** Staff and teachers with access to the course. */
    private function syncMembers(ExamCourse $course, Request $request): void
    {
        $allowed = User::where('type', 'Admin Staff')->pluck('id')->all();
        $sync = [];

        foreach ((array) $request->input('member', []) as $userId => $row) {
            $userId = (int) $userId;
            if (empty($row['on']) || !in_array($userId, $allowed)) {
                continue;
            }
            $sync[$userId] = [
                'member_role' => ($row['role'] ?? 'staff') === 'teacher' ? 'teacher' : 'staff',
                'can_edit' => !empty($row['edit']),
            ];
        }

        $course->members()->sync($sync);
    }

    private function adminOnly(Request $request): void
    {
        abort_unless($request->user()->type === 'Admin', 403, 'Only an Admin can do this.');
    }

    private function authorizeEdit(Request $request, ExamCourse $course): void
    {
        abort_unless($course->canBeEditedBy($request->user()), 403, 'You can view this course but not edit it.');
    }

    private function uniqueSlug(string $title): string
    {
        $base = Str::slug($title) ?: 'course';
        $slug = $base;
        for ($i = 2; ExamCourse::withTrashed()->where('slug', $slug)->exists(); $i++) {
            $slug = $base . '-' . $i;
        }

        return $slug;
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
