@if ($otherCourses->isNotEmpty())
    @php $uid = 'ac' . substr(md5(uniqid('', true)), 0, 6); @endphp
    <div class="col-12">
        <div class="border rounded p-2">
            <div class="d-flex justify-content-between align-items-center">
                <span class="small fw-semibold">Also add this {{ $what ?? 'item' }} to other courses <span class="text-muted fw-normal">(optional - this course is always included)</span></span>
                <label class="small mb-0"><input type="checkbox" class="form-check-input" onchange="document.querySelectorAll('.{{ $uid }}').forEach(function (c) { c.checked = event.target.checked; })"> all</label>
            </div>
            <div class="mt-1" style="max-height:130px;overflow:auto">
                @foreach ($otherCourses as $oc)
                    <div class="form-check"><input class="form-check-input {{ $uid }}" type="checkbox" name="course_ids[]" value="{{ $oc->id }}" id="{{ $uid }}{{ $oc->id }}" @checked(in_array($oc->id, array_map('intval', (array) old('course_ids', []))))><label class="form-check-label small" for="{{ $uid }}{{ $oc->id }}">{{ $oc->title }}</label></div>
                @endforeach
            </div>
            @isset($note)<div class="form-text">{{ $note }}</div>@endisset
        </div>
    </div>
@endif
