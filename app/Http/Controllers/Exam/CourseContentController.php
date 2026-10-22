<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Concerns\ResolvesExam;
use App\Models\ExamCourse;
use App\Models\ExamCourseNode;
use App\Models\ExamFeature;
use App\Services\CourseWorkspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Course content tree for the "tree" features (materials, articles, PYQ, ...):
 * folders > sub folders > files / articles. Notes can be linked to a file (e.g. a question paper).
 *   admin/exam/{exam}/courses/{course}/content/{feature slug}?parent=
 */
class CourseContentController extends Controller
{
    use ResolvesExam, \App\Http\Controllers\Concerns\SiblingCourses;

    private const FILE_MIMES = 'pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,mp3,m4a,aac,wav,ogg,mp4,mov,webm,jpg,jpeg,png,webp';
    private const AUDIO_MIMES = 'mp3,m4a,aac,wav,ogg';
    private const MAX_KB = 102400;   // 100 MB (the server's PHP upload limit must allow it too)

    private const STARTER_FOLDERS = ['Study Materials', 'Syllabus', 'Exam Oriented Special Notes', 'Quick Learn Notes'];

    public function index(Request $request, $examSlug, $courseId, $featureSlug)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, false);

        $parent = $request->filled('parent') ? $this->node($course, $feature, $request->parent) : null;

        $nodes = ExamCourseNode::where('course_id', $course->id)->where('feature_id', $feature->id)
            ->where('parent_id', $parent?->id)
            ->withCount('children')
            ->orderByRaw("node_type = 'folder' desc")->orderBy('position')->orderBy('id')
            ->get();

        $linkCounts = DB::table('exam_course_node_links')->whereIn('node_id', $nodes->pluck('id'))
            ->select('node_id', DB::raw('COUNT(*) as c'))->groupBy('node_id')->pluck('c', 'node_id');

        return view('admin.exam.course-content.index', CourseWorkspace::data($course, $request->user()) + [
            'course' => $course,
            'feature' => $feature,
            'parent' => $parent,
            'nodes' => $nodes,
            'linkCounts' => $linkCounts,
            'breadcrumb' => $parent ? $parent->ancestors()->push($parent) : collect(),
            'allowed' => $feature->allowedNodeTypes(),
            'canEdit' => $course->canBeEditedBy($request->user()),
            'base' => $this->base($course, $feature),
            'exam' => $course->exam,
            'layout' => $course->exam->layout(),
            'courseUrl' => $course->exam->examUrl('courses/' . $course->id),
            'canStarter' => $feature->slug === 'materials' && !$parent && $nodes->isEmpty(),
        ]);
    }

    public function create(Request $request, $examSlug, $courseId, $featureSlug)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);

        $type = in_array($request->type, $feature->allowedNodeTypes()) ? $request->type : $feature->allowedNodeTypes()[0];
        $parent = $request->filled('parent') ? $this->node($course, $feature, $request->parent) : null;

        return view('admin.exam.course-content.form', CourseWorkspace::data($course, $request->user()) + $this->formData($course, $feature, null, $type, $parent?->id));
    }

    public function store(Request $request, $examSlug, $courseId, $featureSlug)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);

        $type = $request->input('node_type');
        abort_unless(in_array($type, $feature->allowedNodeTypes()), 422, 'This feature does not accept that kind of item.');

        $data = $request->validate($this->rules($type, null));
        $this->assertParent($course, $feature, $data['parent_id'] ?? null);
        $this->assertHasContent($type, $request, null);

        $node = new ExamCourseNode();
        $node->course_id = $course->id;
        $node->feature_id = $feature->id;
        $node->node_type = $type;
        $node->created_by = $request->user()->id;
        $this->fill($node, $request, $data, $course, $feature);
        $node->save();

        $this->syncLinks($node, $course, $request);

        $copies = 0;
        foreach ($this->tickedSiblings($request, $course, $feature) as $target) {
            $this->copyNode($node, $target);
            $copies++;
        }

        return redirect($this->base($course, $feature) . ($node->parent_id ? '?parent=' . $node->parent_id : ''))
            ->with('message', ucfirst($type) . ' added' . ($copies ? " (and a copy in {$copies} other course(s), at the top level of this section)" : ''));
    }

    public function edit(Request $request, $examSlug, $courseId, $featureSlug, $nodeId)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        $node = $this->node($course, $feature, $nodeId);

        return view('admin.exam.course-content.form', CourseWorkspace::data($course, $request->user()) + $this->formData($course, $feature, $node, $node->node_type, $node->parent_id));
    }

    public function update(Request $request, $examSlug, $courseId, $featureSlug, $nodeId)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        $node = $this->node($course, $feature, $nodeId);

        $data = $request->validate($this->rules($node->node_type, $node));
        $this->assertParent($course, $feature, $data['parent_id'] ?? null, $node);
        $this->assertHasContent($node->node_type, $request, $node);

        $this->fill($node, $request, $data, $course, $feature);
        $node->save();

        $this->syncLinks($node, $course, $request);

        return redirect($this->base($course, $feature) . ($node->parent_id ? '?parent=' . $node->parent_id : ''))
            ->with('message', ucfirst($node->node_type) . ' updated');
    }

    public function destroy(Request $request, $examSlug, $courseId, $featureSlug, $nodeId)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        $node = $this->node($course, $feature, $nodeId);
        $parentId = $node->parent_id;

        // files of this item and of everything below it
        ExamCourseNode::whereIn('id', array_merge([$node->id], $node->descendantIds()))->get(['file_path', 'audio_path'])
            ->each(function ($n) {
                $this->deleteFile($n->file_path);
                $this->deleteFile($n->audio_path);
            });

        $node->delete();   // sub items and links go with it (foreign key cascade)

        return redirect($this->base($course, $feature) . ($parentId ? '?parent=' . $parentId : ''))->with('message', 'Deleted');
    }

    /** Creates the usual top level folders of a materials feature. */
    public function starter(Request $request, $examSlug, $courseId, $featureSlug)
    {
        [$course, $feature] = $this->resolve($request, $examSlug, $courseId, $featureSlug, true);
        abort_unless($feature->slug === 'materials', 404);

        if (ExamCourseNode::where('course_id', $course->id)->where('feature_id', $feature->id)->exists()) {
            return back()->with('error', 'This section already has content.');
        }

        $now = now();
        foreach (self::STARTER_FOLDERS as $i => $title) {
            ExamCourseNode::create([
                'course_id' => $course->id, 'feature_id' => $feature->id, 'node_type' => 'folder',
                'title' => $title, 'position' => $i + 1, 'status' => true, 'created_by' => $request->user()->id,
            ]);
        }

        return redirect($this->base($course, $feature))->with('message', 'Starter folders created');
    }

    // ------------------------------------------------------------- helpers

    /** Loads the course and a tree feature that is ticked on it, and checks the user's access. */
    private function resolve(Request $request, $examSlug, $courseId, $featureSlug, bool $needsEdit): array
    {
        $exam = $this->exam($examSlug);
        $course = ExamCourse::forExam($exam->id)->findOrFail($courseId);
        $course->setRelation('exam', $exam);

        if ($needsEdit) {
            abort_unless($course->canBeEditedBy($request->user()), 403, 'You can view this course but not edit it.');
        } else {
            abort_unless($course->canBeViewedBy($request->user()), 403, 'You do not have access to this course.');
        }

        $feature = $course->features()->where('exam_features.slug', $featureSlug)->first() ?? abort(404, 'This course does not include that feature.');
        abort_unless($feature->content_kind === 'tree', 404);

        return [$course, ExamFeature::find($feature->id)];
    }

    private function node(ExamCourse $course, ExamFeature $feature, $id): ExamCourseNode
    {
        return ExamCourseNode::where('course_id', $course->id)->where('feature_id', $feature->id)->findOrFail($id);
    }

    private function base(ExamCourse $course, ExamFeature $feature): string
    {
        return $course->exam->examUrl("courses/{$course->id}/content/{$feature->slug}");
    }

    private function rules(string $type, ?ExamCourseNode $node): array
    {
        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|integer',
            'position' => 'nullable|integer|min:0',
            'related_ids' => 'nullable|array',
            'related_ids.*' => 'integer',
        ];

        if ($type === 'file') {
            $rules += [
                'file' => 'nullable|file|mimes:' . self::FILE_MIMES . '|max:' . self::MAX_KB,
                'external_url' => 'nullable|url|max:500',
                'is_free_preview' => 'nullable|boolean',
            ];
        }

        if ($type === 'article') {
            $rules += [
                'body' => 'nullable|string',
                'audio' => 'nullable|file|mimes:' . self::AUDIO_MIMES . '|max:' . self::MAX_KB,
                'is_free_preview' => 'nullable|boolean',
            ];
        }

        return $rules;
    }

    /** A file needs an upload or a link; an article needs text or a voice recording. */
    private function assertHasContent(string $type, Request $request, ?ExamCourseNode $node): void
    {
        $errors = [];

        if ($type === 'file') {
            $hasFile = $request->hasFile('file') || ($node && $node->file_path && !$request->boolean('remove_file'));
            if (!$hasFile && !$request->filled('external_url')) {
                $errors['file'] = 'Upload a file or enter a link.';
            }
        }

        if ($type === 'article') {
            $hasAudio = $request->hasFile('audio') || ($node && $node->audio_path && !$request->boolean('remove_audio'));
            if (!$request->filled('body') && !$hasAudio) {
                $errors['body'] = 'Write the article, or upload its voice recording.';
            }
        }

        if ($errors) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }

    /** The parent must be a folder of the same course and feature - and never the item itself or something below it. */
    private function assertParent(ExamCourse $course, ExamFeature $feature, $parentId, ?ExamCourseNode $self = null): void
    {
        if (!$parentId) {
            return;
        }

        $parent = ExamCourseNode::where('course_id', $course->id)->where('feature_id', $feature->id)->where('node_type', 'folder')->find($parentId);
        $bad = !$parent || ($self && ($parent->id === $self->id || in_array($parent->id, $self->descendantIds())));

        if ($bad) {
            throw \Illuminate\Validation\ValidationException::withMessages(['parent_id' => 'Choose a folder of this section (not the item itself or one of its sub folders).']);
        }
    }

    private function fill(ExamCourseNode $node, Request $request, array $data, ExamCourse $course, ExamFeature $feature): void
    {
        $dir = "exam-courses/{$course->id}/{$feature->slug}";

        $node->title = $data['title'];
        $node->description = $data['description'] ?? null;
        $node->parent_id = !empty($data['parent_id']) ? (int) $data['parent_id'] : null;
        $node->position = $data['position'] ?? 0;
        $node->status = $request->boolean('status');

        if ($node->node_type === 'file') {
            $node->is_free_preview = $request->boolean('is_free_preview');
            $node->external_url = $request->filled('external_url') ? $data['external_url'] : null;

            if ($request->hasFile('file')) {
                $this->deleteFile($node->file_path);
                $file = $request->file('file');
                $node->file_path = $file->store($dir, 'public');
                $node->file_name = $file->getClientOriginalName();
                $node->file_size = $file->getSize();
                $node->file_kind = ExamCourseNode::kindFor($file->extension());
            } elseif ($request->boolean('remove_file')) {
                $this->deleteFile($node->file_path);
                $node->file_path = $node->file_name = $node->file_size = null;
            }

            if (!$node->file_path) {
                $node->file_kind = 'link';
            }
        }

        if ($node->node_type === 'article') {
            $node->is_free_preview = $request->boolean('is_free_preview');
            $node->body = $data['body'] ?? null;

            if ($request->hasFile('audio')) {
                $this->deleteFile($node->audio_path);
                $node->audio_path = $request->file('audio')->store($dir, 'public');
            } elseif ($request->boolean('remove_audio')) {
                $this->deleteFile($node->audio_path);
                $node->audio_path = null;
            }
        }
    }

    /** Notes / solutions linked to a file or article (only items of the same course, never itself). */
    private function syncLinks(ExamCourseNode $node, ExamCourse $course, Request $request): void
    {
        if ($node->node_type === 'folder') {
            return;
        }

        $valid = ExamCourseNode::where('course_id', $course->id)->where('node_type', '!=', 'folder')
            ->whereIn('id', (array) $request->input('related_ids', []))->where('id', '!=', $node->id)->pluck('id')->all();

        $node->links()->sync(collect($valid)->mapWithKeys(fn ($id) => [$id => ['relation' => 'notes']])->all());
    }

    private function formData(ExamCourse $course, ExamFeature $feature, ?ExamCourseNode $node, string $type, $parentId): array
    {
        $all = ExamCourseNode::where('course_id', $course->id)->get(['id', 'feature_id', 'parent_id', 'node_type', 'title']);
        $byId = $all->keyBy('id');

        $path = function ($n) use ($byId) {
            $parts = [$n->title];
            while ($n->parent_id && isset($byId[$n->parent_id])) {
                $n = $byId[$n->parent_id];
                array_unshift($parts, $n->title);
            }

            return implode(' › ', $parts);
        };

        // folders of this section the item can sit in (not itself / below itself)
        $skip = $node ? array_merge([$node->id], $node->descendantIds()) : [];
        $folders = $all->where('feature_id', $feature->id)->where('node_type', 'folder')
            ->reject(fn ($n) => in_array($n->id, $skip))->mapWithKeys(fn ($n) => [$n->id => $path($n)])->sort()->all();

        // notes / papers a file can be linked to
        $linkable = $all->where('node_type', '!=', 'folder')->reject(fn ($n) => $node && $n->id === $node->id)
            ->mapWithKeys(fn ($n) => [$n->id => $path($n)])->sort()->all();

        return [
            'course' => $course,
            'feature' => $feature,
            'node' => $node,
            'type' => $type,
            'parentId' => $parentId,
            'folders' => $folders,
            'linkable' => $linkable,
            'linkedIds' => $node ? $node->links()->pluck('exam_course_nodes.id')->all() : [],
            'base' => $this->base($course, $feature),
            'exam' => $course->exam,
            'layout' => $course->exam->layout(),
            'courseUrl' => $course->exam->examUrl('courses/' . $course->id),
            'otherCourses' => $node ? collect() : $this->siblingCourses(request(), $course, $feature),
        ];
    }

    /** A copy of an item in another course. Uploaded files are copied too, so deleting one never breaks the other. */
    private function copyNode(ExamCourseNode $node, ExamCourse $target): ExamCourseNode
    {
        $copy = $node->replicate();
        $copy->course_id = $target->id;
        $copy->parent_id = null;
        $copy->position = (int) ExamCourseNode::where('course_id', $target->id)->where('feature_id', $node->feature_id)->whereNull('parent_id')->max('position') + 1;
        foreach (['file_path', 'audio_path'] as $col) {
            if ($node->{$col} && Storage::disk('public')->exists($node->{$col})) {
                $new = "exam-courses/{$target->id}/" . \Illuminate\Support\Str::random(40) . '.' . pathinfo($node->{$col}, PATHINFO_EXTENSION);
                Storage::disk('public')->copy($node->{$col}, $new);
                $copy->{$col} = $new;
            } else {
                $copy->{$col} = null;
            }
        }
        $copy->save();

        return $copy;
    }

    private function deleteFile(?string $path): void
    {
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }
}
