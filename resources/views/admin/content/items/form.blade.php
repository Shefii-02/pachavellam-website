@extends('admin.main-layout')
@section('main')
@php $editing = (bool) $item; $has = fn ($f) => in_array($f, $m['fields']); $v = fn ($f, $d = '') => old($f, $item->{$f} ?? $d); $date = fn ($f) => old($f, $item && $item->{$f} ? $item->{$f}->format('Y-m-d') : ''); @endphp
<div class="page-content-wrapper border">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">{{ $editing ? 'Edit' : 'New' }} {{ $m['one'] }}</h3>
        <a href="{{ url('admin/content/' . $m['section']) }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="card bg-transparent border rounded-3"><div class="card-body">
        <form action="{{ $editing ? url('admin/content/' . $m['section'] . '/' . $item->id) : url('admin/content/' . $m['section']) }}" method="POST" enctype="multipart/form-data">
            @csrf @if ($editing) @method('PUT') @endif
            <div class="row g-3">
                @if (empty($m['global']))<div class="col-12">@include('admin.content._exam-picker', ['exams' => $exams, 'chosen' => $chosen, 'what' => $m['one']])</div>@endif

                @if (in_array('folder_id', $m['fields']))
                    @php $folderRows = \App\Models\ContentFolder::with('exam:id,short_name,name')->where('kind', $m['kind'])->orderBy('exam_id')->orderBy('title')->get(); $selFolder = old('folder_id', $item->folder_id ?? ($preFolder ?? '')); @endphp
                    <div class="col-md-6"><label class="form-label">Folder</label>
                        <select name="folder_id" class="form-select"><option value="">Top level (no folder)</option>
                            @foreach ($folderRows as $fr)<option value="{{ $fr->id }}" {{ (string) $selFolder === (string) $fr->id ? 'selected' : '' }}>{{ ($fr->exam->short_name ?? $fr->exam->name) . ' / ' . $fr->path() }}</option>@endforeach
                        </select>@error('folder_id')<div class="text-danger small">{{ $message }}</div>@enderror
                        <div class="form-text">Make folders on the list page. The folder must be of an exam type ticked above.</div></div>
                @endif
                @if ($has('category_id'))
                    @php $catRows = \App\Models\ExamCategory::with('exam:id,short_name,name')->where('status', 1)->orderBy('exam_id')->orderBy('position')->orderBy('id')->get(); $selCat = old('category_id', $item->category_id ?? ''); @endphp
                    <div class="col-md-6"><label class="form-label">Category / sub category</label>
                        <select name="category_id" id="itCat" class="form-select"><option value="">Not in a category</option>
                            @foreach ($catRows as $cr)<option value="{{ $cr->id }}" data-exam="{{ $cr->exam_id }}" {{ (string) $selCat === (string) $cr->id ? 'selected' : '' }}>{{ ($cr->exam->short_name ?? $cr->exam->name) . ' / ' . $cr->ancestors()->push($cr)->pluck('name')->implode(' › ') }}</option>@endforeach
                        </select>@error('category_id')<div class="text-danger small">{{ $message }}</div>@enderror
                        <div class="form-text">Shows on the category's own page (/c/category-slug) with its sub categories.</div></div>
                    <script>document.addEventListener('DOMContentLoaded', function () { var s = document.getElementById('itCat'); function f() { var on = [].map.call(document.querySelectorAll('.exam-box:checked'), function (b) { return b.value; }); [].forEach.call(s.options, function (o) { if (!o.value) return; var show = !on.length || on.indexOf(o.getAttribute('data-exam')) > -1 || o.selected; o.hidden = !show; o.disabled = !show; }); } document.addEventListener('change', function (e) { if (e.target.classList && e.target.classList.contains('exam-box')) f(); }); f(); });</script>
                @endif
                @if ($has('audio_url'))
                    <div class="col-md-6"><label class="form-label">Recorded voice - link</label><input class="form-control" type="url" name="audio_url" maxlength="500" value="{{ $v('audio_url') }}" placeholder="https://… .mp3">@error('audio_url')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                    <div class="col-md-6"><label class="form-label">…or upload the voice file</label><input class="form-control" type="file" name="audio" accept="audio/*">@error('audio')<div class="text-danger small">{{ $message }}</div>@enderror
                        @if ($editing && $item->audio)<div class="form-check mt-1"><input class="form-check-input" type="checkbox" name="remove_audio" value="1" id="rmAudio"><label class="form-check-label small" for="rmAudio">Remove the uploaded voice ({{ basename($item->audio) }})</label></div>@endif
                        <div class="form-text">Leave both empty and the website / app reads the article aloud (text-to-speech).</div></div>
                @endif
                @if ($has('title'))<div class="col-md-8"><label class="form-label">{{ ['partner' => 'Company name', 'faq' => 'Question'][$m['kind']] ?? 'Title' }} @if ($m['kind'] !== 'banner')<span class="text-danger">*</span>@endif</label><input class="form-control" name="title" maxlength="255" value="{{ $v('title') }}" {{ $m['kind'] !== 'banner' ? 'required' : '' }}>@error('title')<div class="text-danger small">{{ $message }}</div>@enderror</div>@endif
                @if ($has('type'))
                    @if ($m['kind'] === 'team_member')
                        <div class="col-md-4"><label class="form-label">Group</label><select name="type" class="form-select"><option value="head" {{ $v('type', 'head') === 'head' ? 'selected' : '' }}>Head office</option><option value="office" {{ $v('type') === 'office' ? 'selected' : '' }}>Office</option></select></div>
                    @elseif ($m['kind'] === 'faq')
                        <div class="col-md-4"><label class="form-label">Group (tab)</label><select name="type" class="form-select">@foreach (\App\Models\ContentItem::FAQ_GROUPS as $gk => $gl)<option value="{{ $gk }}" {{ $v('type', 'general') === $gk ? 'selected' : '' }}>{{ $gl }}</option>@endforeach</select></div>
                    @elseif ($m['kind'] === 'footer_link')
                        <div class="col-md-4"><label class="form-label">Footer column <span class="text-danger">*</span></label><input class="form-control" name="type" required maxlength="30" list="fgroups" value="{{ $v('type') }}" placeholder="e.g. Company"><datalist id="fgroups"><option value="Company"><option value="Popular goals"><option value="Products"><option value="Legal"></datalist></div>
                    @else
                        <div class="col-md-4"><label class="form-label">Type</label><select name="type" class="form-select"><option value="exam" {{ $v('type', 'exam') === 'exam' ? 'selected' : '' }}>Exam</option><option value="other" {{ $v('type') === 'other' ? 'selected' : '' }}>Other</option></select></div>
                    @endif
                @endif
                @if ($has('image'))
                    @php $imgSpec = ['banner' => 'banner', 'achievement' => 'story', 'team_member' => 'square', 'partner' => 'logo', 'key_point' => 'icon'][$m['kind']] ?? 'cover'; @endphp
                    <div class="col-md-8"><label class="form-label">{{ ['partner' => 'Logo / icon', 'key_point' => 'Icon (optional)'][$m['kind']] ?? 'Image' }} @if ($m['kind'] === 'banner')<span class="text-danger">*</span>@endif</label>
                        <div class="pvm-field" data-name="image" data-kind="image" data-spec="{{ $imgSpec }}" @if ($m['kind'] === 'banner') data-required="1" @endif
                             @if ($editing && $item->image) data-value="{{ $item->image }}" data-url="{{ Storage::url('files/' . $item->image) }}" @endif></div>
                        @error('image')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                @endif
                @if ($m['kind'] === 'promo_class')
                    <div class="col-md-8"><label class="form-label">YouTube link <span class="text-danger">*</span></label><input class="form-control" name="link_url" maxlength="500" required value="{{ $v('link_url') }}" placeholder="https://www.youtube.com/watch?v=… or live link">@error('link_url')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                    <div class="col-md-4"><label class="form-label">Class type <span class="text-danger">*</span></label><select name="mode" id="pcMode" class="form-select"><option value="recorded" {{ $v('mode', 'recorded') === 'recorded' ? 'selected' : '' }}>Recorded</option><option value="live" {{ $v('mode') === 'live' ? 'selected' : '' }}>Live (scheduled)</option></select></div>
                    <div class="col-md-4"><label class="form-label">Class chat</label><select name="chat_mode" class="form-select">@foreach (['off' => 'Off', 'live' => 'On - only during the live class', 'always' => 'On - always (also recorded)'] as $cv => $cl)<option value="{{ $cv }}" {{ $v('chat_mode', 'off') === $cv ? 'selected' : '' }}>{{ $cl }}</option>@endforeach</select><div class="form-text">"Only during the live class" opens 10 min before the live time and closes 3 h after.</div></div>
                                        <div class="col-md-4" id="pcWhen"><label class="form-label">Live at <span class="text-danger">*</span></label><input type="datetime-local" class="form-control" name="scheduled_at" value="{{ old('scheduled_at', $item && $item->scheduled_at ? $item->scheduled_at->format('Y-m-d\TH:i') : '') }}">@error('scheduled_at')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                    <script>document.addEventListener('DOMContentLoaded', function () { var m = document.getElementById('pcMode'), w = document.getElementById('pcWhen'); function p() { w.style.display = m.value === 'live' ? '' : 'none'; } m.addEventListener('change', p); p(); });</script>
                @elseif ($m['kind'] === 'partner')
                    <div class="col-md-8"><label class="form-label">Redirect URL</label><input class="form-control" type="url" name="link_url" maxlength="500" value="{{ $v('link_url') }}" placeholder="https://partner-website.com">@error('link_url')<div class="text-danger small">{{ $message }}</div>@enderror<div class="form-text">Opens in a new tab when the logo is clicked.</div></div>
                @elseif ($has('link_url'))
                    <div class="col-12">@include('admin.content._landing-picker', ['type' => $item->target_type ?? 'link', 'value' => $item->target_value ?? ''])</div>
                    <div class="col-md-8" data-landing-link><label class="form-label">Custom link</label><input class="form-control" id="landingLink" name="link_url" maxlength="500" value="{{ $v('link_url') }}" placeholder="https://…">@error('link_url')<div class="text-danger small">{{ $message }}</div>@enderror</div>
                @endif
                @if ($has('description'))<div class="col-12"><label class="form-label">{{ $m['kind'] === 'article' ? 'Article text' : 'Details' }}</label><textarea class="form-control" name="description" rows="{{ $m['kind'] === 'article' ? 10 : 4 }}">{{ $v('description') }}</textarea></div>@endif
                @if ($has('published_at'))<div class="col-md-3"><label class="form-label">Publish date</label><input type="date" class="form-control" name="published_at" value="{{ $date('published_at') }}"></div>@endif
                @if ($has('last_date'))<div class="col-md-3"><label class="form-label">Last date to apply</label><input type="date" class="form-control" name="last_date" value="{{ $date('last_date') }}"></div>@endif
                @if ($has('starts_at'))<div class="col-md-3"><label class="form-label">Show from</label><input type="date" class="form-control" name="starts_at" value="{{ $date('starts_at') }}"></div>@endif
                @if ($has('ends_at'))<div class="col-md-3"><label class="form-label">Show until</label><input type="date" class="form-control" name="ends_at" value="{{ $date('ends_at') }}">@error('ends_at')<div class="text-danger small">{{ $message }}</div>@enderror</div>@endif
                @if ($has('bg_color'))<div class="col-md-3"><label class="form-label">Background colour</label><input type="color" class="form-control form-control-color" name="bg_color" value="{{ $v('bg_color', '#ffffff') }}"></div>@endif
                @if ($has('text_color'))<div class="col-md-3"><label class="form-label">Text colour</label><input type="color" class="form-control form-control-color" name="text_color" value="{{ $v('text_color', '#000000') }}"></div>@endif
                @if ($has('attachment'))<div class="col-md-8"><label class="form-label">Attachment (PDF / Office / image)</label>
                    <div class="pvm-field" data-name="attachment" data-kind="file" data-spec="file" @if ($editing && $item->attachment) data-value="{{ $item->attachment }}" data-url="{{ Storage::url('files/' . $item->attachment) }}" data-label="Current file" @endif></div>
                    @error('attachment')<div class="text-danger small">{{ $message }}</div>@enderror</div>@endif

                <div class="col-md-2"><label class="form-label">Position</label><input type="number" min="0" class="form-control" name="position" value="{{ $v('position') }}" placeholder="auto"></div>
                <div class="col-md-10 d-flex align-items-end gap-4 flex-wrap">
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="status" id="st" value="1" {{ old('status', $editing ? $item->status : 1) ? 'checked' : '' }}><label class="form-check-label" for="st">Shown</label></div>
                    @if ($has('new_tab'))<div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="new_tab" id="nt" value="1" {{ old('new_tab', $item->new_tab ?? false) ? 'checked' : '' }}><label class="form-check-label" for="nt">Open link in a new tab</label></div>@endif
                    @if ($has('on_home'))<div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="on_home" id="oh" value="1" {{ old('on_home', $item->on_home ?? false) ? 'checked' : '' }}><label class="form-check-label" for="oh">Show on the home page (Latest YouTube classes)</label></div>@endif
                    @if ($has('is_pinned'))<div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="is_pinned" id="pn" value="1" {{ old('is_pinned', $item->is_pinned ?? false) ? 'checked' : '' }}><label class="form-check-label" for="pn">Pin to top</label></div>@endif
                </div>
            </div>

            @if (in_array($m['kind'], ['announcement', 'notification', 'whats_new', 'article', 'banner']))
                <div class="border rounded p-3 mt-4">
                    <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" name="send_push" id="sp" value="1" {{ old('send_push') ? 'checked' : '' }}><label class="form-check-label fw-semibold" for="sp"><i class="bi bi-bell me-1"></i>Also send as a push notification when I save</label></div>
                    <div id="pushBox" style="display:none" class="row g-2 mt-2">
                        <div class="col-md-5"><input class="form-control form-control-sm" name="push_title" maxlength="120" placeholder="Push title (default: the title)" value="{{ old('push_title') }}"></div>
                        <div class="col-md-7"><input class="form-control form-control-sm" name="push_body" maxlength="200" placeholder="Push text (default: the first words of the details)" value="{{ old('push_body') }}"></div>
                        <div class="col-md-8"><select name="push_audience" class="form-select form-select-sm">
                            <option value="exams" {{ old('push_audience', 'exams') === 'exams' ? 'selected' : '' }}>Students interested in the exam types ticked above</option>
                            <option value="interested" {{ old('push_audience') === 'interested' ? 'selected' : '' }}>Students interested in any exam type</option>
                            <option value="all" {{ old('push_audience') === 'all' ? 'selected' : '' }}>Everyone who has the app or web push on</option>
                        </select></div>
                        <div class="col-12 small text-muted">Goes to every device of each person - Android, iOS and web. Tapping it opens the landing page chosen above.</div>
                    </div>
                </div>
                <script>document.addEventListener('DOMContentLoaded', function () { var c = document.getElementById('sp'), b = document.getElementById('pushBox'); function p() { b.style.display = c.checked ? '' : 'none'; } c.addEventListener('change', p); p(); });</script>
            @endif
            <div class="mt-4"><button class="btn btn-success mb-0"><i class="bi bi-check me-1"></i>Save</button> <a href="{{ url('admin/content/' . $m['section']) }}" class="btn btn-light mb-0">Cancel</a></div>
        </form>
    </div></div>
</div>
@endsection
