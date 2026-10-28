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
        <select name="target_course" id="tgCourse" class="form-select tg tg-course" style="display:none;width:100%"><option value="">Search and choose the course…</option>@foreach ($opt['courses'] as $c)<option value="{{ $c->id }}" data-exam="{{ $c->exam_id }}" {{ $type === 'course' && (string) $value === (string) $c->id ? 'selected' : '' }}>[{{ $c->exam->short_name ?? $c->exam->name ?? '-' }}] {{ $c->title }} · {{ $c->isFree() ? 'Free' : '₹' . rtrim(rtrim(number_format($c->effectivePrice(), 2), '0'), '.') }}</option>@endforeach</select>
        <div class="form-text tg tg-course" style="display:none">Only courses of the exam types ticked above are listed (none ticked = all courses). Type to search.</div>
        <select name="target_exam" id="tgExam" class="form-select tg tg-exam" style="display:none;width:100%"><option value="">Choose the exam type…</option>@foreach ($opt['exams'] as $e)<option value="{{ $e->slug }}" {{ $type === 'exam' && $value === $e->slug ? 'selected' : '' }}>{{ $e->short_name ?: $e->name }}</option>@endforeach</select>
        <select name="target_page" class="form-select tg tg-page" style="display:none"><option value="">Choose the page…</option>@foreach ($opt['pages'] as $k => $label)<option value="{{ $k }}" {{ $type === 'page' && $value === $k ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select>
        <div class="form-text tg tg-link">Type the address in the link box.</div>
    </div>
</div>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<style>.select2-container{width:100%!important}.select2-container .select2-selection--single{height:38px;border:1px solid #dee2e6}.select2-container--default .select2-selection--single .select2-selection__rendered{line-height:36px}.select2-container--default .select2-selection--single .select2-selection__arrow{height:36px}</style>
<script>
    (function () {
        function boot() {
            var $ = window.jQuery;
            var sel = document.getElementById('tgType'), link = document.getElementById('landingLink');
            var allCourses = $('#tgCourse option').map(function () { return { id: this.value, text: this.text, exam: this.getAttribute('data-exam') }; }).get();
            var chosen = $('#tgCourse').val();

            // courses of the ticked exam types only (nothing ticked = every course); the picked one stays even if its exam gets unticked
            function ticked() { return $('.exam-box:checked').map(function () { return this.value; }).get(); }
            function fill() {
                var ex = ticked(), keep = $('#tgCourse').val();
                var $c = $('#tgCourse').empty().append('<option value="">Search and choose the course…</option>');
                allCourses.forEach(function (o) {
                    if (!o.id) return;
                    if (!ex.length || ex.indexOf(o.exam) > -1 || o.id === keep) $c.append($('<option>').val(o.id).text(o.text).attr('data-exam', o.exam));
                });
                $c.val(keep || chosen).trigger('change.select2');
            }
            function paint() {
                var t = sel.value;
                document.querySelectorAll('.tg').forEach(function (e) {
                    var on = e.classList.contains('tg-' + t);
                    e.style.display = on ? '' : 'none';
                    var w = e.nextElementSibling; if (w && w.classList.contains('select2-container')) w.style.display = on ? '' : 'none';
                });
                if (link) { var box = link.closest('[data-landing-link]') || link.parentElement; box.style.display = (t === 'link') ? '' : 'none'; }
            }
            $('#tgCourse, #tgExam').select2({ placeholder: 'Search…', allowClear: true });
            fill(); paint();
            sel.addEventListener('change', paint);
            document.addEventListener('change', function (e) { if (e.target.classList && e.target.classList.contains('exam-box')) fill(); });
            ['examAll', 'examNone'].forEach(function (id) { var a = document.getElementById(id); if (a) a.addEventListener('click', function () { setTimeout(fill, 0); }); });
        }
        function load(src, ok) { var s = document.createElement('script'); s.src = src; s.onload = ok; document.head.appendChild(s); }
        document.addEventListener('DOMContentLoaded', function () {
            var withSelect2 = function () { if (window.jQuery.fn.select2) boot(); else load('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js', boot); };
            if (window.jQuery) withSelect2(); else load('https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js', withSelect2);
        });
    })();
</script>
