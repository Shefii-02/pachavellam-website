{{-- Exam type checkboxes. Needs: $exams, $chosen (ids ticked), optional $what ("capsule" / "story") --}}
@php $picked = (array) old('exam_ids', $chosen ?? []); @endphp
<label class="form-label">Show in these exam types <span class="text-danger">*</span></label>
<div class="border rounded p-3">
    <div class="d-flex flex-wrap gap-3">
        @foreach ($exams as $e)
            <div class="form-check">
                <input class="form-check-input exam-box" type="checkbox" name="exam_ids[]" value="{{ $e->id }}" id="ex{{ $e->id }}" {{ in_array($e->id, $picked) ? 'checked' : '' }}>
                <label class="form-check-label" for="ex{{ $e->id }}">
                    <span class="badge text-white" style="background:{{ $e->color ?: '#6c757d' }}">{{ $e->short_name ?: $e->name }}</span>
                    @unless ($e->status)<small class="text-muted">(hidden)</small>@endunless
                </label>
            </div>
        @endforeach
    </div>
    <div class="mt-2 small">
        <a href="#" id="examAll">Tick all</a> · <a href="#" id="examNone">Clear</a>
        <span class="text-muted ms-2">The {{ $what ?? 'item' }} only appears for the exam types ticked here.</span>
    </div>
</div>
@error('exam_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var boxes = document.querySelectorAll('.exam-box');
        document.getElementById('examAll').addEventListener('click', function (e) { e.preventDefault(); boxes.forEach(function (b) { b.checked = true; }); });
        document.getElementById('examNone').addEventListener('click', function (e) { e.preventDefault(); boxes.forEach(function (b) { b.checked = false; }); });
    });
</script>
