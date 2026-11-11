{{-- The permission part of the role form (create and edit): exam types, permissions section by section, and what the role sees in each exam type.
     Needs $permission, $groups, $other, $rolePermissions, $exams, $roleExams, $examFeatures, $enabled. --}}
@php
    $byName = $permission->keyBy('name');
    $chosenExams = array_map('intval', old('exam_ids', $roleExams));
    $ticked = old('permission') ? array_map('intval', old('permission')) : array_map('intval', array_keys($rolePermissions));
@endphp
<div class="card bg-transparent border rounded-3 mb-3"><div class="card-body">
    <div class="d-flex justify-content-between"><h6>Exam types</h6><label class="small mb-0"><input type="checkbox" class="form-check-input" id="allExams"> all</label></div>
    <div class="d-flex flex-wrap gap-3">
        @foreach ($exams as $e)
            <div class="form-check"><input class="form-check-input exam-check" type="checkbox" name="exam_ids[]" value="{{ $e->id }}" id="re{{ $e->id }}" @checked(in_array($e->id, $chosenExams))><label class="form-check-label" for="re{{ $e->id }}">{{ $e->short_name ?: $e->name }}</label></div>
        @endforeach
    </div>
    <div class="form-text mt-2">Everyone who has this role can work in these exam types (added to the exam types ticked for the person in Staff Access).</div>
</div></div>

<div class="card bg-transparent border rounded-3 mb-3"><div class="card-body">
    <div class="d-flex justify-content-between"><h6>Permissions <span class="small text-muted fw-normal">View = open and read. Manage = view plus add, edit and delete.</span></h6><label class="small mb-0"><input type="checkbox" id="checkAll" class="form-check-input"> check all</label></div>
    <div class="row g-3">
        @foreach ($groups as $group => $perms)
            <div class="col-md-6 col-xl-4">
                <div class="fw-semibold mb-2">{{ $group }}</div>
                @foreach (collect($perms)->groupBy('feature') as $feature => $levels)
                    <div class="border rounded-3 p-2 mb-2">
                        <div class="fw-medium">{{ $feature }}</div>
                        <div class="d-flex flex-wrap gap-3 mt-1">
                            @foreach ($perms as $name => $cfg)
                                @continue($cfg['feature'] !== $feature || !isset($byName[$name]))
                                <div class="form-check mb-0">
                                    <input class="form-check-input perm-box perm-{{ $cfg['level'] }}" data-feature="{{ md5($feature) }}" data-name="{{ $name }}" type="checkbox" name="permission[]" value="{{ $byName[$name]->id }}" id="rp{{ $byName[$name]->id }}" @checked(in_array($byName[$name]->id, $ticked))>
                                    <label class="form-check-label" for="rp{{ $byName[$name]->id }}">{{ $cfg['level'] === 'view' ? 'View' : ($levels->count() > 1 ? 'Manage' : 'Use') }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
        @endforeach
    </div>
    @if ($other->count())
        <div class="fw-semibold mt-3 mb-2">Other permissions</div>
        <div class="row">
            @foreach ($other as $value)
                <div class="col-md-4 mb-1"><label><input type="checkbox" class="form-check-input perm-box" data-name="{{ $value->name }}" name="permission[]" value="{{ $value->id }}" @checked(in_array($value->id, $ticked))> {{ $value->name }}</label></div>
            @endforeach
        </div>
    @endif
</div></div>

<div class="card bg-transparent border rounded-3 mb-3"><div class="card-body">
    <h6>What this role sees in each exam type <span class="small text-muted fw-normal">updates as you tick the permissions above; an exam type only has the features chosen in General &gt; Exam Types</span></h6>
    <div class="accordion" id="roleExamView">
        @foreach ($exams as $e)
            <div class="accordion-item">
                <h2 class="accordion-header"><button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#rev{{ $e->id }}">{{ $e->name }} <span class="badge bg-light text-dark border ms-2 role-count" data-exam="{{ $e->id }}"></span></button></h2>
                <div id="rev{{ $e->id }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}" data-bs-parent="#roleExamView">
                    <div class="accordion-body">
                        <div class="row g-3">
                            @foreach ($examFeatures[$e->id] as $group => $items)
                                <div class="col-md-4">
                                    <div class="fw-semibold small text-uppercase text-muted mb-1">{{ $group }}</div>
                                    @foreach ($items as $key => $item)
                                        <div class="role-feature small" data-exam="{{ $e->id }}" data-permission="{{ $item['permission'] }}" data-on="{{ in_array($key, $enabled[$e->id]) ? 1 : 0 }}">
                                            <i class="bi me-1"></i>{{ $item['label'] }} <span class="text-muted role-why"></span>
                                        </div>
                                    @endforeach
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div></div>

<script>
(function () {
    var boxes = function () { return document.querySelectorAll('input.perm-box'); };
    var has = function (name) { return !!document.querySelector('input.perm-box[data-name="' + name + '"]:checked'); };
    var can = function (perm) { return !perm || has(perm + ' view') || has(perm + ' manage') || (perm === 'New joins' && has('New joins mark')); };
    function refresh() {
        var counts = {};
        document.querySelectorAll('.role-feature').forEach(function (el) {
            var exam = el.dataset.exam, on = el.dataset.on === '1', ok = on && can(el.dataset.permission);
            el.querySelector('i').className = 'bi me-1 ' + (ok ? 'bi-check-circle-fill text-success' : 'bi-dash-circle text-muted');
            el.classList.toggle('text-muted', !ok);
            el.querySelector('.role-why').textContent = ok ? '' : (on ? '· needs ' + el.dataset.permission + ' View' : '· not in this exam type');
            counts[exam] = (counts[exam] || 0) + (ok ? 1 : 0);
        });
        document.querySelectorAll('.role-count').forEach(function (b) { b.textContent = (counts[b.dataset.exam] || 0) + ' features'; });
    }
    boxes().forEach(function (b) { b.addEventListener('change', refresh); });
    // ticking Manage also ticks View; clearing View also clears Manage
    document.querySelectorAll('.perm-manage').forEach(function (m) {
        var v = document.querySelector('.perm-view[data-feature="' + m.dataset.feature + '"]');
        if (!v) { return; }
        m.addEventListener('change', function () { if (m.checked) { v.checked = true; refresh(); } });
        v.addEventListener('change', function () { if (!v.checked) { m.checked = false; refresh(); } });
    });
    document.getElementById('checkAll').addEventListener('change', function () { var c = this.checked; boxes().forEach(function (b) { b.checked = c; }); refresh(); });
    var all = document.getElementById('allExams');
    all.addEventListener('change', function () { document.querySelectorAll('.exam-check').forEach(function (c) { c.checked = all.checked; }); });
    refresh();
})();
</script>
