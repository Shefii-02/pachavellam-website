{{-- Where a tap lands. Needs: $type, $value; the link field is the page's own "link_url"/"redirection" input (id landingLink). --}}
@php $opt = \App\Support\Landing::options(); $type = old('target_type', $type ?? 'link'); $value = old('target_course', old('target_exam', old('target_page', $value ?? ''))); @endphp
<label class="form-label">When tapped, open</label>
<div class="row g-2">
    <div class="col-md-4">
        <select name="target_type" id="tgType" class="form-select">
            @foreach (\App\Support\Landing::TYPES as $k => $label)<option value="{{ $k }}" {{ $type === $k ? 'selected' : '' }}>{{ $label }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-8">
        <select name="target_course" class="form-select tg tg-course" style="display:none"><option value="">Choose the course…</option>@foreach ($opt['courses'] as $c)<option value="{{ $c->id }}" {{ $type === 'course' && (string) $value === (string) $c->id ? 'selected' : '' }}>{{ $c->title }}</option>@endforeach</select>
        <select name="target_exam" class="form-select tg tg-exam" style="display:none"><option value="">Choose the exam type…</option>@foreach ($opt['exams'] as $e)<option value="{{ $e->slug }}" {{ $type === 'exam' && $value === $e->slug ? 'selected' : '' }}>{{ $e->short_name ?: $e->name }}</option>@endforeach</select>
        <select name="target_page" class="form-select tg tg-page" style="display:none"><option value="">Choose the page…</option>@foreach ($opt['pages'] as $k => $label)<option value="{{ $k }}" {{ $type === 'page' && $value === $k ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select>
        <div class="form-text tg tg-link">Type the address in the link box.</div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var sel = document.getElementById('tgType'), link = document.getElementById('landingLink');
        function paint() {
            var t = sel.value;
            document.querySelectorAll('.tg').forEach(function (e) { e.style.display = e.classList.contains('tg-' + t) ? '' : 'none'; });
            if (link) { var box = link.closest('[data-landing-link]') || link.parentElement; box.style.display = (t === 'link') ? '' : 'none'; }
        }
        sel.addEventListener('change', paint); paint();
    });
</script>
