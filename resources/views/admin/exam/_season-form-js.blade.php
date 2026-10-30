{{-- "Built around" decides what the next field is: a subject / category / exam of this exam type to pick, or a free text. Needs $exam. --}}
@php
    $pvBasis = [
        'subject' => \App\Models\ExamSubject::where('exam_id', $exam->id)->where('status', 1)->orderBy('position')->orderBy('title')->pluck('title')->unique()->values(),
        'category' => \App\Models\ExamCategory::where('exam_id', $exam->id)->orderBy('name')->pluck('name')->unique()->values(),
        'exam' => \App\Models\CompetitiveExam::where('status', 1)->orderBy('position')->pluck('name')->unique()->values(),
    ];
@endphp
<script>
    window.PV_BASIS = @json($pvBasis);
    var PV_BASIS_LABEL = { subject: 'Which subject', category: 'Which category', exam: 'Which exam', other: 'Name / details' };
    function pvBasisChange(p, first) {
        var basis = document.getElementById(p + 'basis').value, wrap = document.getElementById(p + 'bnWrap'), sel = document.getElementById(p + 'bnSel'), txt = document.getElementById(p + 'bnTxt');
        var cur = first ? (txt.dataset.cur || '') : (sel.style.display === 'none' ? txt.value : sel.value);
        wrap.style.display = basis ? '' : 'none';
        document.getElementById(p + 'bnLbl').textContent = PV_BASIS_LABEL[basis] || 'Which subject / category / exam';
        var list = (window.PV_BASIS[basis] || []).slice();
        if (list.length) {
            if (cur && list.indexOf(cur) < 0) list.unshift(cur);   // a saved value that is not in the list any more stays selectable
            sel.innerHTML = '<option value="">Choose…</option>' + list.map(function (n) { return '<option value="' + n.replace(/"/g, '&quot;') + '"' + (n === cur ? ' selected' : '') + '>' + n.replace(/</g, '&lt;') + '</option>'; }).join('');
            sel.style.display = ''; sel.disabled = !basis; txt.style.display = 'none'; txt.disabled = true;
        } else {   // "Other", or nothing to pick from yet: type it
            txt.value = cur; txt.placeholder = basis && basis !== 'other' ? 'Nothing added yet - type a name' : 'e.g. Indian History';
            txt.style.display = ''; txt.disabled = !basis; sel.style.display = 'none'; sel.disabled = true;
        }
    }
    document.querySelectorAll('select[id$="basis"]').forEach(function (b) { pvBasisChange(b.id.replace(/basis$/, ''), true); });
</script>
