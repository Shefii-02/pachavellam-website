<?php

namespace App\Http\Controllers\General;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Services\StudentPerformance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Exam types (KPSC, RRB, SSC, KTET, UGC NET, and any exam added later): General > Exam Types. Admin only.
 * One row here gives the exam its own tab in the main sidebar, its categories, courses and coupons,
 * and lets students pick it as an interest.
 */
class ExamTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user() && $request->user()->type === 'Admin', 403, 'Only an Admin can manage exam types.');

            return $next($request);
        });
    }

    public function index()
    {
        $exams = CompetitiveExam::orderBy('position')->orderBy('id')->get();

        $count = fn (string $table) => DB::table($table)->select('exam_id', DB::raw('COUNT(*) as c'))->groupBy('exam_id')->pluck('c', 'exam_id');

        return view('admin.general.exam-types.index', [
            'exams' => $exams,
            'students' => $count('student_exam_interests'),
            'categories' => $count('exam_categories'),
            'courses' => $count('exam_courses'),
        ]);
    }

    public function create()
    {
        return view('admin.general.exam-types.form', ['exam' => null, 'sources' => $this->sources()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(($data['slug'] ?? null) ?: $data['name']);
        $data['image'] = $request->hasFile('image') ? $request->file('image')->store('exam-types', 'public') : null;

        CompetitiveExam::create($data);

        return redirect(admin_general('exam-types'))->with('message', 'Exam type added. It now has its own tab in the main sidebar and students can pick it as an interest.');
    }

    public function edit($id)
    {
        return view('admin.general.exam-types.form', ['exam' => CompetitiveExam::findOrFail($id), 'sources' => $this->sources()]);
    }

    public function update(Request $request, $id)
    {
        $exam = CompetitiveExam::findOrFail($id);
        $data = $this->validated($request);
        unset($data['slug']); // the slug is used in urls and by the app, so it never changes
        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            if ($exam->image) {
                Storage::disk('public')->delete($exam->image);
            }
            $data['image'] = $request->hasFile('image') ? $request->file('image')->store('exam-types', 'public') : null;
        }

        $exam->update($data);

        return redirect(admin_general('exam-types'))->with('message', 'Exam type updated');
    }

    public function destroy($id)
    {
        $exam = CompetitiveExam::findOrFail($id);

        $inUse = [
            'students are interested in it' => 'student_exam_interests',
            'it has categories' => 'exam_categories',
            'it has courses' => 'exam_courses',
        ];
        foreach ($inUse as $why => $table) {
            if (DB::table($table)->where('exam_id', $exam->id)->exists()) {
                return back()->with('error', "This exam type cannot be deleted because {$why}. Hide it instead.");
            }
        }
        if (isset(CompetitiveExam::MODULES[$exam->slug])) {
            return back()->with('error', 'This exam type has its own admin module, so it cannot be deleted. Hide it instead.');
        }

        $exam->delete();

        return redirect(admin_general('exam-types'))->with('message', 'Exam type deleted');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:30',
            'slug' => 'nullable|string|max:60|regex:/^[a-z0-9\-]+$/|unique:competitive_exams,slug',
            'description' => 'nullable|string',
            'color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'performance_source' => 'nullable|in:' . implode(',', array_keys(StudentPerformance::SOURCES)),
            'position' => 'nullable|integer|min:0',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        unset($data['image']); // the file is stored by store() / update()

        $data['short_name'] = $data['short_name'] ?? null;
        $data['description'] = $data['description'] ?? null;
        $data['color'] = $data['color'] ?? null;
        $data['performance_source'] = $data['performance_source'] ?? null;
        $data['position'] = $data['position'] ?? 0;
        $data['status'] = $request->boolean('status');

        return $data;
    }

    private function uniqueSlug(string $base): string
    {
        $slug = Str::slug($base) ?: 'exam';
        $try = $slug;
        for ($i = 2; CompetitiveExam::where('slug', $try)->exists(); $i++) {
            $try = $slug . '-' . $i;
        }

        return $try;
    }

    private function sources(): array
    {
        return collect(StudentPerformance::SOURCES)->map(fn ($s, $k) => $k . ' — ' . $s['label'])->all();
    }
}
