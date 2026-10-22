@extends($layout)
@section('content2')
@php
    $editing = (bool) $course;
    $val = fn ($key, $default = '') => old($key, $course->{$key} ?? $default);
    $lines = fn ($v) => is_array($v) ? implode("\n", $v) : $v;
    $examName = $exam->short_name ?: $exam->name;
    $back = $editing ? $base . '/' . $course->id : $base;

    // the steps; features and staff are an Admin decision, so other editors do not see those two
    $steps = [
        ['basics', 'Basics', 'Title, description, level'],
        ['pricing', 'Pricing & validity', 'Free or paid, dates'],
        ['learn', 'Learning & media', 'Outcomes, cover, video'],
        ['categories', 'Categories', 'Where students find it'],
    ];
    if ($isAdmin) {
        $steps[] = ['features', 'Features', 'Content, exams, related'];
        $steps[] = ['staff', 'Staff & teachers', 'Who looks after it'];
    }
    $steps[] = ['seo', 'Search & order', 'Meta text, position'];
@endphp

<form action="{{ $editing ? $base . '/' . $course->id : $base }}" method="POST" enctype="multipart/form-data" id="courseForm" novalidate>
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
        <div>
            <h3 class="mb-0">{{ $editing ? 'Edit course' : 'New course' }}</h3>
            <div class="small text-muted"><span class="badge text-white" style="background:{{ $exam->color ?: '#6c757d' }}">{{ $examName }}</span> {{ $exam->name }}@if ($editing) · {{ $course->title }}@endif</div>
        </div>
        <a href="{{ $back }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-arrow-left me-1"></i>{{ $editing ? 'Back to the course' : 'Cancel' }}</a>
    </div>

    <div class="row g-4">
        {{-- the side stepper --}}
        <div class="col-lg-3">
            <div class="card bg-transparent border rounded-3">
                <div class="list-group list-group-flush rounded-3" id="stepper">
                    @foreach ($steps as $i => [$key, $label, $hint])
                        <a href="#" class="list-group-item list-group-item-action step-item d-flex align-items-center" data-step="{{ $i }}">
                            <span class="step-no rounded-circle bg-light text-dark border d-inline-flex align-items-center justify-content-center me-3 flex-shrink-0" style="width:30px;height:30px;font-weight:600">{{ $i + 1 }}</span>
                            <span class="flex-grow-1">
                                <span class="d-block">{{ $label }}</span>
                                <small class="d-block opacity-75">{{ $hint }}</small>
                            </span>
                            <i class="bi bi-exclamation-circle-fill text-danger step-error" style="display:none"></i>
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="small text-muted mt-2">Step <span id="stepNow">1</span> of {{ count($steps) }}</div>
            <div class="progress mt-1" style="height:5px"><div class="progress-bar" id="stepBar" style="width:0"></div></div>
        </div>

        {{-- the steps --}}
        <div class="col-lg-9">
            @if ($errors->any())<div class="alert alert-danger py-2">Please fix the highlighted fields.</div>@endif

            {{-- 1 basics --}}
            <div class="card bg-transparent border rounded-3 step-panel" data-key="basics">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Basics</h5></div>
                <div class="card-body"><div class="row g-3">
                    <div class="col-md-8">
                        <label class="form-label">Course title <span class="text-danger">*</span></label>
                        <input class="form-control" required name="title" value="{{ $val('title') }}" placeholder="e.g. {{ $examName }} Prelims 2027 – Full Course">
                        @error('title')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Status</label>
                        <select class="form-control" name="status">
                            @foreach (\App\Models\ExamCourse::STATUSES as $k => [$label])
                                <option value="{{ $k }}" @selected($val('status', 'draft') === $k)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">Draft is hidden from students.</div>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Short description</label>
                        <input class="form-control" name="short_description" maxlength="300" value="{{ $val('short_description') }}" placeholder="One line shown on the course card">
                        @error('short_description')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label">Full description</label>
                        <textarea class="form-control" rows="6" name="description">{{ $val('description') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Level</label>
                        <select class="form-control" name="level">
                            @foreach (\App\Models\ExamCourse::LEVELS as $k => $label)<option value="{{ $k }}" @selected($val('level', 'all') === $k)>{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Language</label>
                        <select class="form-control" name="language">
                            @foreach (\App\Models\ExamCourse::LANGUAGES as $k => $label)<option value="{{ $k }}" @selected($val('language', 'ml') === $k)>{{ $label }}</option>@endforeach
                        </select>
                    </div>
                </div></div>
            </div>

            {{-- 2 pricing --}}
            <div class="card bg-transparent border rounded-3 step-panel" data-key="pricing" style="display:none">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Pricing &amp; validity</h5></div>
                <div class="card-body"><div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Course type</label>
                        <select class="form-control" name="course_type" id="courseType">
                            <option value="free" @selected($val('course_type', 'free') === 'free')>Free</option>
                            <option value="paid" @selected($val('course_type', 'free') === 'paid')>Paid</option>
                        </select>
                    </div>
                    <div class="col-md-4 paid-only">
                        <label class="form-label">Price (₹) <span class="text-danger">*</span></label>
                        <input class="form-control" type="number" step="0.01" min="0" name="price" id="price" value="{{ $val('price', 0) }}">
                        @error('price')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 paid-only">
                        <label class="form-label">Discount price (₹)</label>
                        <input class="form-control" type="number" step="0.01" min="0" name="discount_price" value="{{ $val('discount_price') }}">
                        @error('discount_price')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Access validity</label>
                        <select class="form-control" name="validity_type" id="validityType">
                            <option value="lifetime" @selected($val('validity_type', 'lifetime') === 'lifetime')>Lifetime</option>
                            <option value="days" @selected($val('validity_type', 'lifetime') === 'days')>Days after joining</option>
                            <option value="date" @selected($val('validity_type', 'lifetime') === 'date')>Until a fixed date</option>
                        </select>
                    </div>
                    <div class="col-md-4 v-days">
                        <label class="form-label">Days <span class="text-danger">*</span></label>
                        <input class="form-control" type="number" min="1" name="validity_days" id="validityDays" value="{{ $val('validity_days') }}">
                        @error('validity_days')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4 v-date">
                        <label class="form-label">Valid until <span class="text-danger">*</span></label>
                        <input class="form-control" type="date" name="valid_until" id="validUntil" value="{{ old('valid_until', optional($course->valid_until ?? null)->format('Y-m-d')) }}">
                        @error('valid_until')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Course starts</label>
                        <input class="form-control" type="date" name="starts_on" value="{{ old('starts_on', optional($course->starts_on ?? null)->format('Y-m-d')) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Course ends</label>
                        <input class="form-control" type="date" name="ends_on" value="{{ old('ends_on', optional($course->ends_on ?? null)->format('Y-m-d')) }}">
                        @error('ends_on')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total duration (hours)</label>
                        <input class="form-control" type="number" step="0.5" min="0" name="duration_hours" value="{{ $val('duration_hours') }}">
                    </div>
                </div></div>
            </div>

            {{-- 3 learning + media --}}
            <div class="card bg-transparent border rounded-3 step-panel" data-key="learn" style="display:none">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Learning &amp; media</h5></div>
                <div class="card-body"><div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">What you will learn <span class="text-muted small">(one point per line)</span></label>
                        <textarea class="form-control" rows="5" name="highlights">{{ old('highlights', $lines($course->highlights ?? [])) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Requirements <span class="text-muted small">(one per line)</span></label>
                        <textarea class="form-control" rows="5" name="requirements">{{ old('requirements', $lines($course->requirements ?? [])) }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Who is this course for?</label>
                        <textarea class="form-control" rows="3" name="audience">{{ $val('audience') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tags</label>
                        <input class="form-control" name="tags" value="{{ old('tags', implode(', ', $course->tags ?? [])) }}" placeholder="degree level, prelims, 2027">
                        <div class="form-text">Separate with commas.</div>
                        <div class="form-check mt-2"><input class="form-check-input" type="checkbox" name="certificate_enabled" value="1" id="cert" {{ old('certificate_enabled', $course->certificate_enabled ?? false) ? 'checked' : '' }}><label class="form-check-label" for="cert">Certificate on completion</label></div>
                        <div class="form-check"><input class="form-check-input" type="checkbox" name="is_featured" value="1" id="feat" {{ old('is_featured', $course->is_featured ?? false) ? 'checked' : '' }}><label class="form-check-label" for="feat">Featured course</label></div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Cover image</label>
                        <input class="form-control" type="file" name="cover_image" accept="image/*">
                        @error('cover_image')<div class="text-danger small">{{ $message }}</div>@enderror
                        @if ($editing && $course->cover_image)
                            <div class="mt-2 d-flex align-items-center gap-2">
                                <img src="{{ asset('storage/' . $course->cover_image) }}" alt="" style="height:48px;border-radius:6px">
                                <div class="form-check"><input class="form-check-input" type="checkbox" name="remove_cover" value="1" id="rc"><label class="form-check-label" for="rc">Remove</label></div>
                            </div>
                        @endif
                    </div>
                    <div class="col-md-7">
                        <label class="form-label">Promo video link</label>
                        <input class="form-control" type="url" name="promo_video_url" value="{{ $val('promo_video_url') }}" placeholder="https://www.youtube.com/watch?v=...">
                        @error('promo_video_url')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                </div></div>
            </div>

            {{-- 4 categories --}}
            <div class="card bg-transparent border rounded-3 step-panel" data-key="categories" style="display:none">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">Categories</h5>
                    <div class="small text-muted">Pick any number. Students filter courses by these.</div>
                </div>
                <div class="card-body">
                    @php $pickedCats = (array) old('category_ids', $selectedCategories); @endphp
                    <div class="border rounded p-2" style="max-height:340px;overflow:auto">
                        @forelse ($categories as $id => $label)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="category_ids[]" value="{{ $id }}" id="cat{{ $id }}" {{ in_array($id, $pickedCats) ? 'checked' : '' }}>
                                <label class="form-check-label" for="cat{{ $id }}">{{ $label }}</label>
                            </div>
                        @empty
                            <div class="text-muted small p-2">No categories for {{ $examName }} yet. <a href="{{ $exam->examUrl('category') }}" target="_blank">Create some</a>, then reload this page.</div>
                        @endforelse
                    </div>
                    @error('category_ids.*')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
            </div>

            @if ($isAdmin)
            {{-- 5 features --}}
            <div class="card bg-transparent border rounded-3 step-panel" data-key="features" style="display:none">
                <div class="card-header bg-transparent border-bottom d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Features</h5>
                        <div class="small text-muted">Tick what the course includes. After saving, each one is managed from the course's side menu.</div>
                    </div>
                    <a href="{{ admin_general('features') }}" class="small" target="_blank">Manage features</a>
                </div>
                <div class="card-body"><div class="row g-3">
                    @foreach ($features as $f)
                        @php
                            $sel = $selected->get($f->id);
                            $checked = old('feature_ids') !== null ? in_array($f->id, (array) old('feature_ids')) : (bool) $sel;
                            $scope = old("scope.{$f->id}", $sel->pivot->scope ?? 'common');
                            $subs = old("categories.{$f->id}", $sel ? (json_decode($sel->pivot->category_ids ?? '[]', true) ?: []) : []);
                        @endphp
                        <div class="col-md-6">
                            <div class="border rounded p-3 h-100 feature-box">
                                <div class="form-check">
                                    <input class="form-check-input feature-check" type="checkbox" name="feature_ids[]" value="{{ $f->id }}" id="f{{ $f->id }}" {{ $checked ? 'checked' : '' }}>
                                    <label class="form-check-label fw-bold" for="f{{ $f->id }}">@include('admin.partials.feature-icon', ['feature' => $f, 'size' => 22]){{ $f->name }}</label>
                                    <span class="badge bg-light text-dark border ms-1">{{ ['tree' => 'content', 'exam' => 'exams', 'classes' => 'classes', 'flag' => 'related'][$f->content_kind] ?? '' }}</span>
                                </div>
                                <div class="small text-muted ms-4">{{ $f->description }}</div>

                                @if ($f->supports_subjects)
                                    <div class="ms-4 mt-2 feature-options" style="{{ $checked ? '' : 'display:none' }}">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input scope-radio" type="radio" name="scope[{{ $f->id }}]" value="common" id="sc{{ $f->id }}a" {{ $scope !== 'category' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="sc{{ $f->id }}a">Common (all categories)</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input scope-radio" type="radio" name="scope[{{ $f->id }}]" value="category" id="sc{{ $f->id }}b" {{ $scope === 'category' ? 'checked' : '' }}>
                                            <label class="form-check-label" for="sc{{ $f->id }}b">Specific categories / subjects</label>
                                        </div>
                                        <div class="subject-pick mt-2" style="{{ $scope === 'category' ? '' : 'display:none' }}">
                                            <select class="form-control" name="categories[{{ $f->id }}][]" multiple size="7">
                                                @foreach ($categories as $cid => $clabel)
                                                    <option value="{{ $cid }}" @selected(in_array($cid, $subs))>{{ $clabel }}</option>
                                                @endforeach
                                            </select>
                                            <div class="form-text">Categories of {{ $examName }}. Hold Ctrl / Cmd to pick several.</div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div></div>
            </div>

            {{-- 6 staff --}}
            <div class="card bg-transparent border rounded-3 step-panel" data-key="staff" style="display:none">
                <div class="card-header bg-transparent border-bottom">
                    <h5 class="mb-0">Staff &amp; teachers</h5>
                    <div class="small text-muted">They see this course in their list. “Can edit” lets them change its details and content.</div>
                </div>
                <div class="card-body p-0 table-responsive">
                    <table class="table align-middle mb-0">
                        <thead><tr><th style="width:60px">Access</th><th>Name</th><th style="width:180px">Role in course</th><th style="width:110px">Can edit</th></tr></thead>
                        <tbody>
                            @forelse ($people as $p)
                                @php
                                    $a = $assigned->get($p->id);
                                    $on = old('member') !== null ? !empty(old("member.{$p->id}.on")) : (bool) $a;
                                    $role = old("member.{$p->id}.role", $a->pivot->member_role ?? (in_array($p->id, $teacherIds) ? 'teacher' : 'staff'));
                                    $edit = old('member') !== null ? !empty(old("member.{$p->id}.edit")) : ($a ? (bool) $a->pivot->can_edit : true);
                                @endphp
                                <tr>
                                    <td><input class="form-check-input" type="checkbox" name="member[{{ $p->id }}][on]" value="1" {{ $on ? 'checked' : '' }}></td>
                                    <td>{{ $p->name }} <span class="small text-muted">{{ $p->email }}</span>@if (in_array($p->id, $teacherIds))<span class="badge bg-info text-white">Teacher role</span>@endif</td>
                                    <td><select class="form-select form-select-sm" name="member[{{ $p->id }}][role]"><option value="teacher" @selected($role === 'teacher')>Teacher</option><option value="staff" @selected($role === 'staff')>Staff</option></select></td>
                                    <td><input class="form-check-input" type="checkbox" name="member[{{ $p->id }}][edit]" value="1" {{ $edit ? 'checked' : '' }}></td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-muted text-center py-3">No staff users yet. Add them under General → Admin Staff.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- last: search + order --}}
            <div class="card bg-transparent border rounded-3 step-panel" data-key="seo" style="display:none">
                <div class="card-header bg-transparent border-bottom"><h5 class="mb-0">Search &amp; order</h5></div>
                <div class="card-body"><div class="row g-3">
                    <div class="col-md-5"><label class="form-label">Meta title</label><input class="form-control" name="meta_title" value="{{ $val('meta_title') }}"></div>
                    <div class="col-md-5"><label class="form-label">Meta description</label><input class="form-control" name="meta_description" value="{{ $val('meta_description') }}"></div>
                    <div class="col-md-2"><label class="form-label">Position</label><input class="form-control" type="number" min="0" name="position" value="{{ $val('position', 0) }}"></div>
                </div></div>
            </div>

            {{-- buttons --}}
            <div class="d-flex justify-content-between align-items-center mt-3 mb-4">
                <button type="button" class="btn btn-light mb-0" id="btnBack"><i class="bi bi-arrow-left me-1"></i>Back</button>
                <div class="d-flex gap-2">
                    @if ($editing)<button type="submit" class="btn btn-outline-success mb-0" id="btnSaveNow"><i class="bi bi-check me-1"></i>Save changes</button>@endif
                    <button type="button" class="btn btn-primary mb-0" id="btnNext">Next <i class="bi bi-arrow-right ms-1"></i></button>
                    <button type="submit" class="btn btn-success mb-0" id="btnFinish"><i class="bi bi-check-circle me-1"></i>{{ $editing ? 'Save changes' : 'Create course' }}</button>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<style>
    #stepper .step-item.active { background: var(--bs-primary, #0d6efd); border-color: var(--bs-primary, #0d6efd); }
    #stepper .step-item.active .step-no { background: #fff !important; color: var(--bs-primary, #0d6efd) !important; }
    #stepper .step-item.done .step-no { background: #198754 !important; color: #fff !important; border-color: #198754 !important; }
</style>
<script>
(function () {
    var panels = Array.prototype.slice.call(document.querySelectorAll('.step-panel'));
    var items = Array.prototype.slice.call(document.querySelectorAll('.step-item'));
    var form = document.getElementById('courseForm');
    var editing = {{ $editing ? 'true' : 'false' }};
    var current = 0;

    function show(n) {
        current = Math.max(0, Math.min(panels.length - 1, n));
        panels.forEach(function (p, i) { p.style.display = i === current ? '' : 'none'; });
        items.forEach(function (it, i) {
            it.classList.toggle('active', i === current);
            it.classList.toggle('done', i < current && !it.classList.contains('active'));
        });
        document.getElementById('stepNow').textContent = current + 1;
        document.getElementById('stepBar').style.width = ((current + 1) / panels.length * 100) + '%';
        document.getElementById('btnBack').style.visibility = current === 0 ? 'hidden' : 'visible';
        var last = current === panels.length - 1;
        document.getElementById('btnNext').style.display = last ? 'none' : '';
        document.getElementById('btnFinish').style.display = (last || editing) ? '' : 'none';
        var saveNow = document.getElementById('btnSaveNow');
        if (saveNow) { saveNow.style.display = last ? 'none' : ''; }
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    // the fields that are required right now (hidden inputs of an unused section are skipped)
    function syncRequired() {
        var paid = document.getElementById('courseType').value === 'paid';
        document.getElementById('price').required = paid;
        document.getElementById('validityDays').required = document.getElementById('validityType').value === 'days';
        document.getElementById('validUntil').required = document.getElementById('validityType').value === 'date';
    }

    // true when the field sits inside something the form itself hid (e.g. the price of a free course); the step being hidden does not count
    function hiddenInside(field, panel) {
        for (var el = field; el && el !== panel; el = el.parentElement) {
            if (el.style && el.style.display === 'none') { return true; }
        }
        return false;
    }

    function firstInvalid(panel) {
        var fields = panel.querySelectorAll('input, select, textarea');
        for (var i = 0; i < fields.length; i++) {
            var f = fields[i];
            if (f.disabled || hiddenInside(f, panel)) { continue; }
            if (!f.checkValidity()) { return f; }
        }
        return null;
    }

    document.getElementById('btnNext').addEventListener('click', function () {
        syncRequired();
        var bad = firstInvalid(panels[current]);
        if (bad) { bad.reportValidity(); return; }
        show(current + 1);
    });
    document.getElementById('btnBack').addEventListener('click', function () { show(current - 1); });
    items.forEach(function (it, i) {
        it.addEventListener('click', function (e) {
            e.preventDefault();
            if (i > current) {   // going forward checks the steps in between, so nothing required is skipped on a new course
                syncRequired();
                for (var s = current; s < i; s++) {
                    var bad = firstInvalid(panels[s]);
                    if (bad && !editing) { show(s); bad.reportValidity(); return; }
                }
            }
            show(i);
        });
    });

    // on submit, jump to the first step that has a problem
    form.addEventListener('submit', function (e) {
        syncRequired();
        for (var i = 0; i < panels.length; i++) {
            var bad = firstInvalid(panels[i]);
            if (bad) { e.preventDefault(); show(i); setTimeout(function () { bad.reportValidity(); }, 50); return; }
        }
    });

    // price / validity show and hide
    function money() { var paid = document.getElementById('courseType').value === 'paid'; document.querySelectorAll('.paid-only').forEach(function (el) { el.style.display = paid ? '' : 'none'; }); syncRequired(); }
    function validity() { var v = document.getElementById('validityType').value; document.querySelectorAll('.v-days').forEach(function (el) { el.style.display = v === 'days' ? '' : 'none'; }); document.querySelectorAll('.v-date').forEach(function (el) { el.style.display = v === 'date' ? '' : 'none'; }); syncRequired(); }
    document.getElementById('courseType').addEventListener('change', money);
    document.getElementById('validityType').addEventListener('change', validity);
    money(); validity();

    // features: scope options
    document.querySelectorAll('.feature-check').forEach(function (c) { c.addEventListener('change', function () { var o = c.closest('.feature-box').querySelector('.feature-options'); if (o) { o.style.display = c.checked ? '' : 'none'; } }); });
    document.querySelectorAll('.scope-radio').forEach(function (r) { r.addEventListener('change', function () { var box = r.closest('.feature-options').querySelector('.subject-pick'); box.style.display = (r.value === 'category' && r.checked) ? '' : 'none'; }); });

    // after a failed save: mark the steps with errors and open the first one
    var firstError = -1;
    panels.forEach(function (p, i) {
        if (p.querySelector('.text-danger.small')) { items[i].querySelector('.step-error').style.display = ''; if (firstError < 0) { firstError = i; } }
    });
    show(firstError >= 0 ? firstError : 0);
})();
</script>
@endsection
