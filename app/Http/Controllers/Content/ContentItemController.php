<?php

namespace App\Http\Controllers\Content;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Models\ContentFolder;
use App\Models\ContentItem;
use App\Support\StaffAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Banners, What's New, Announcements, Articles and Notifications (admin/content/{section}).
 * They are common to every exam type: the exam types are chosen when the item is created.
 */
class ContentItemController extends Controller
{
    private function meta(string $section): array
    {
        abort_unless(isset(ContentItem::KINDS[$section]), 404);

        return ContentItem::KINDS[$section] + ['section' => $section];
    }

    private function allow(Request $request): void
    {
        StaffAccess::authorize($request, 'Content manage');
    }

    public function index(Request $request, string $section)
    {
        $this->allow($request);
        $m = $this->meta($section);
        $exams = CompetitiveExam::orderBy('position')->get();

        // folder based sections (syllabus, study materials, results) browse one exam type's folders
        $folderMode = !empty($m['folders']) && $request->filled('exam') && $exams->firstWhere('slug', $request->exam);
        $exam = $folderMode ? $exams->firstWhere('slug', $request->exam) : null;
        $current = null;
        $subfolders = collect();
        if ($folderMode) {
            $current = $request->filled('folder') ? ContentFolder::where('exam_id', $exam->id)->where('kind', $m['kind'])->find($request->folder) : null;
            $subfolders = ContentFolder::where('exam_id', $exam->id)->where('kind', $m['kind'])->where('parent_id', $current?->id)->orderBy('position')->orderBy('title')->get();
            $counts = ContentItem::ofKind($m['kind'])->forExam($exam->slug)->selectRaw('folder_id, count(*) c')->groupBy('folder_id')->pluck('c', 'folder_id');
            $childCounts = ContentFolder::where('exam_id', $exam->id)->where('kind', $m['kind'])->selectRaw('parent_id, count(*) c')->groupBy('parent_id')->pluck('c', 'parent_id');
            $subfolders->each(fn ($f) => [$f->items_count = (int) ($counts[$f->id] ?? 0), $f->folders_count = (int) ($childCounts[$f->id] ?? 0)]);
        }

        $list = ContentItem::with('exams:id,short_name,name,color', 'folder:id,title,parent_id')->ofKind($m['kind'])
            ->when($request->filled('exam'), fn ($q) => $q->forExam($request->exam))
            ->when($folderMode, fn ($q) => $q->where('folder_id', $current?->id))
            ->when(in_array($request->status, ['0', '1'], true), fn ($q) => $q->where('status', $request->status))
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', '%' . $request->q . '%'))
            ->orderByDesc('is_pinned')->orderByDesc('position')->orderByDesc('id')->paginate(25)->withQueryString();

        return view('admin.content.items.index', ['m' => $m, 'list' => $list, 'exams' => $exams, 'folderMode' => (bool) $folderMode, 'exam' => $exam, 'current' => $current, 'subfolders' => $subfolders,
            'allFolders' => $folderMode ? ContentFolder::where('exam_id', $exam->id)->where('kind', $m['kind'])->orderBy('title')->get() : collect()]);
    }

    /** Achievements: bring in the stories of an Instagram highlight. */
    public function importForm(Request $request, string $section)
    {
        $this->allow($request);
        abort_unless($section === 'achievements', 404);

        return view('admin.content.items.import', ['m' => $this->meta($section)]);
    }

    public function importStore(Request $request, string $section)
    {
        $this->allow($request);
        abort_unless($section === 'achievements', 404);
        $data = $request->validate(['payload' => 'required|string|max:5000000', 'highlight_url' => 'nullable|url|max:500', 'exam_ids' => 'nullable|array', 'exam_ids.*' => 'integer|exists:competitive_exams,id', 'back' => 'nullable|in:stories']);
        try {
            $r = app(\App\Services\AchievementImporter::class)->import($data['payload'], $data['highlight_url'] ?? null, $request->user()->id, array_map('intval', $data['exam_ids'] ?? []));
        } catch (\InvalidArgumentException $e) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage(), 'errors' => ['payload' => [$e->getMessage()]]], 422);
            }

            return back()->withInput()->withErrors(['payload' => $e->getMessage()]);
        }
        if ($request->expectsJson()) {
            return response()->json($r);   // added, skipped, repaired, failed, remaining, errors
        }

        $toStories = ($data['back'] ?? null) === 'stories';
        $msg = "Synced: {$r['added']} new, {$r['skipped']} already there" . ($r['repaired'] ? ", {$r['repaired']} missing file(s) restored" : '') . ($r['failed'] ? ", {$r['failed']} failed (" . implode('; ', array_slice($r['errors'], 0, 2)) . ')' : '');
        if ($r['remaining'] > 0) {
            // a big highlight is done in rounds: the paste is kept, press Sync again to carry on
            return redirect(url('admin/stories'))->withInput(['payload' => $data['payload'], 'highlight_url' => $data['highlight_url'] ?? null, 'exam_ids' => $data['exam_ids'] ?? []])
                ->with('message', $msg . ". {$r['remaining']} more to go - press Sync now again to continue.")->with('sync_open', true);
        }

        return redirect(url('admin/stories'))->with('message', $msg . '.');
    }

    public function create(Request $request, string $section)
    {
        $this->allow($request);

        return view('admin.content.items.form', $this->formData($this->meta($section), null, $request->exam));
    }

    public function store(Request $request, string $section)
    {
        $this->allow($request);
        $m = $this->meta($section);
        $data = $this->validated($request, $m, null);

        [$tt, $tv] = \App\Support\Landing::fromRequest($request);
        $item = new ContentItem(array_merge($data['fields'], ['kind' => $m['kind'], 'created_by' => $request->user()->id, 'target_type' => $tt, 'target_value' => $tv]));
        $this->files($request, $item);
        $item->status = $request->boolean('status');
        $item->save();
        if (!$item->position) {
            $item->position = $item->id;
            $item->save();
        }
        $item->exams()->sync($data['exam_ids']);
        $pushed = $this->maybePush($request, $item, $data['exam_ids']);

        return redirect($this->backUrl($section, $item))->with('message', ucfirst($m['one']) . ' added' . $pushed);
    }

    public function edit(Request $request, string $section, $id)
    {
        $this->allow($request);
        $m = $this->meta($section);

        return view('admin.content.items.form', $this->formData($m, ContentItem::ofKind($m['kind'])->findOrFail($id), null));
    }

    public function update(Request $request, string $section, $id)
    {
        $this->allow($request);
        $m = $this->meta($section);
        $item = ContentItem::ofKind($m['kind'])->findOrFail($id);
        $data = $this->validated($request, $m, $item);

        $item->fill($data['fields']);
        [$item->target_type, $item->target_value] = \App\Support\Landing::fromRequest($request);
        $this->files($request, $item);
        $item->status = $request->boolean('status');
        $item->save();
        $item->exams()->sync($data['exam_ids']);
        $pushed = $this->maybePush($request, $item, $data['exam_ids']);

        return redirect($this->backUrl($section, $item))->with('message', ucfirst($m['one']) . ' updated' . $pushed);
    }

    public function destroy(Request $request, string $section, $id)
    {
        $this->allow($request);
        $m = $this->meta($section);
        ContentItem::ofKind($m['kind'])->findOrFail($id)->delete();   // soft delete

        return redirect(url('admin/content/' . $section))->with('message', ucfirst($m['one']) . ' deleted');
    }

    public function toggle(Request $request, string $section, $id)
    {
        $this->allow($request);
        $item = ContentItem::ofKind($this->meta($section)['kind'])->findOrFail($id);
        $item->status = !$item->status;
        $item->save();

        return back()->with('message', $item->status ? 'Now shown' : 'Now hidden');
    }

    // ------------------------------------------------------------- helpers

    /** Back to the list; folder sections return to the folder the item is in. */
    private function backUrl(string $section, ContentItem $item): string
    {
        if (empty(ContentItem::KINDS[$section]['folders'])) {
            return url('admin/content/' . $section);
        }
        $folder = $item->folder_id ? ContentFolder::find($item->folder_id) : null;
        $examSlug = $folder?->exam?->slug ?? $item->exams()->value('slug');

        return url('admin/content/' . $section . '?exam=' . $examSlug . ($folder ? '&folder=' . $folder->id : ''));
    }

    /** "Also send as a push notification" ticked: every device of the people interested in the chosen exam types. */
    private function maybePush(Request $request, ContentItem $item, array $examIds): string
    {
        if (!$request->boolean('send_push') || !in_array($item->kind, ['announcement', 'notification', 'whats_new', 'article', 'banner', 'promo_class'], true)) {
            return '';
        }
        $audience = in_array($request->input('push_audience'), ['all', 'interested', 'exams'], true) ? $request->input('push_audience') : 'exams';
        $campaign = \App\Services\PushNotifier::create([
            'title' => $request->input('push_title') ?: ($item->title ?: 'Pachavellam'),
            'body' => $request->input('push_body') ?: \Illuminate\Support\Str::limit(strip_tags((string) $item->description) ?: ($item->title ?: 'Tap to open'), 140),
            'image' => $item->image, 'target_type' => $item->target_type, 'target_value' => $item->target_type === 'link' ? $item->link_url : $item->target_value,
            'audience' => $audience, 'audience_exam_ids' => $audience === 'exams' ? $examIds : null,
            'content_item_id' => $item->id, 'created_by' => $request->user()->id,
        ]);
        dispatch(fn () => app(\App\Services\PushNotifier::class)->send($campaign))->afterResponse();

        return ' - push notification is being sent';
    }

    private function formData(array $m, ?ContentItem $item, $preselect): array
    {
        $exams = CompetitiveExam::orderBy('position')->get();
        $chosen = $item ? $item->exams()->pluck('competitive_exams.id')->all() : $exams->filter(fn ($e) => $e->slug === $preselect)->pluck('id')->all();

        return ['m' => $m, 'item' => $item, 'exams' => $exams, 'chosen' => $chosen, 'preFolder' => request('folder')];
    }

    private function validated(Request $request, array $m, ?ContentItem $item): array
    {
        $rules = [
            'title' => ['nullable', 'string', 'max:255'], 'description' => ['nullable', 'string', 'max:20000'], 'link_url' => ['nullable', 'string', 'max:500'],
            'type' => ['nullable', 'in:exam,other'], 'bg_color' => ['nullable', 'string', 'max:20'], 'text_color' => ['nullable', 'string', 'max:20'],
            'starts_at' => ['nullable', 'date'], 'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'], 'published_at' => ['nullable', 'date'], 'last_date' => ['nullable', 'date'],
            'position' => ['nullable', 'integer', 'min:0'],
            'subject_id' => ['nullable', 'integer', 'exists:exam_subjects,id'], 'folder_id' => ['nullable', 'integer', 'exists:content_folders,id'], 'mode' => ['nullable', 'in:live,recorded'], 'chat_mode' => ['nullable', 'in:off,live,always'], 'scheduled_at' => ['nullable', 'date'],
            'image' => [$item && $item->image || $request->filled('image_media') || !in_array('image', $m['fields']) ? 'nullable' : ($m['kind'] === 'banner' ? 'required' : 'nullable'), 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:4096'],
            'image_media' => ['nullable', 'string', 'max:400'], 'attachment_media' => ['nullable', 'string', 'max:400'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png', 'max:10240'],
            'exam_ids' => ['required', 'array', 'min:1'], 'exam_ids.*' => ['integer', 'exists:competitive_exams,id'],
        ];
        if (in_array($m['kind'], ['whats_new', 'announcement', 'article', 'notification', 'promo_class', 'syllabus', 'material', 'result'], true)) {
            $rules['title'] = ['required', 'string', 'max:255'];
        }
        if ($m['kind'] === 'promo_class') {
            $rules['link_url'] = ['required', 'string', 'max:500', function ($attr, $value, $fail) {
                if (!\App\Services\ClassSource::youtubeId((string) $value)) {
                    $fail('Paste a YouTube link (watch, live or youtu.be address).');
                }
            }];
            $rules['mode'] = ['required', 'in:live,recorded'];
            $rules['scheduled_at'] = ['nullable', 'date', 'required_if:mode,live'];
        }
        if (!empty($m['global'])) {
            $rules['exam_ids'] = ['nullable', 'array'];
            $rules['title'] = ['required', 'string', 'max:255'];
            $rules['type'] = $m['kind'] === 'team_member' ? ['nullable', 'in:head,office'] : ['nullable', 'string', 'max:30'];
            if ($m['kind'] === 'faq') {
                $rules['type'] = ['nullable', 'in:' . implode(',', array_keys(ContentItem::FAQ_GROUPS))];
            }
            if ($m['kind'] === 'partner') {
                $rules['link_url'] = ['nullable', 'url', 'max:500'];
            }
            if ($m['kind'] === 'footer_link') {
                $rules['link_url'] = ['required', 'string', 'max:500'];
                $rules['type'] = ['required', 'string', 'max:30'];
            }
        }
        $data = $request->validate($rules, ['exam_ids.required' => 'Tick at least one exam type.', 'exam_ids.min' => 'Tick at least one exam type.']);

        if (!empty($data['folder_id'])) {
            $folder = ContentFolder::find($data['folder_id']);
            if (!$folder || $folder->kind !== $m['kind'] || !in_array($folder->exam_id, array_map('intval', $data['exam_ids'] ?? []), true)) {
                throw \Illuminate\Validation\ValidationException::withMessages(['folder_id' => 'Choose a folder of one of the ticked exam types.']);
            }
        }
        $fields = collect($data)->only(array_merge($m['fields'], ['position']))->except(['image', 'attachment', 'new_tab', 'is_pinned'])->all();
        $fields['new_tab'] = $request->boolean('new_tab');
        $fields['is_pinned'] = $request->boolean('is_pinned');
        if ($m['kind'] === 'promo_class') {
            $fields['on_home'] = $request->boolean('on_home');
        }
        if (empty($fields['position'])) {
            unset($fields['position']);
        }

        return ['fields' => $fields, 'exam_ids' => array_values(array_unique(array_map('intval', $data['exam_ids'] ?? [])))];
    }

    /**
     * Pictures / files of an item: chosen from the media library (field_media = path, nothing is copied) or uploaded the old way.
     * A file that is in the media library is shared, so it is never deleted when an item stops using it.
     */
    private function files(Request $request, ContentItem $item): void
    {
        foreach (['image' => 'content', 'attachment' => 'content-files'] as $field => $dir) {
            $old = $item->{$field};
            $chosen = trim((string) $request->input($field . '_media'));
            if ($chosen !== '' && \App\Models\Media::where('path', 'files/' . $chosen)->exists() && !str_contains($chosen, '..')) {
                $item->{$field} = $chosen;
            } elseif ($request->hasFile($field)) {
                $file = $request->file($field);
                $name = Str::random(40) . '.' . $file->extension();
                $file->storeAs('files/' . $dir, $name, 'public');
                $item->{$field} = $dir . '/' . $name;
            } elseif ($request->boolean('remove_' . $field) && $old) {
                $item->{$field} = null;
            } else {
                continue;
            }
            if ($old && $old !== $item->{$field} && !\App\Models\Media::where('path', 'files/' . $old)->exists()) {
                Storage::disk('public')->delete('files/' . $old);
            }
        }
    }
}
