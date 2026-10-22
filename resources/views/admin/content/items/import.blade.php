@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><i class="bi bi-instagram me-1"></i>Import Instagram highlight</h3>
        <a href="{{ url('admin/content/achievements') }}" class="btn btn-sm btn-light mb-0"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
    <div class="alert alert-info small">
        <b>How</b>
        <ol class="mb-0 ps-3">
            <li>Open your highlight link in a story downloader in your own browser (for example snapgram.io) and press Download.</li>
            <li>Copy the answer the downloader returns (the JSON starting with <code>{"code":200</code>) - in the browser press F12, open the Network tab, click the <code>dl?url=...</code> request and copy its Response.</li>
            <li>Paste it below. The pictures and videos (with sound) are copied onto our own storage at once, because Instagram's links expire within a day. Stories already imported are skipped.</li>
        </ol>
    </div>
    <div class="card bg-transparent border rounded-3"><div class="card-body">
        <form method="POST" action="{{ url('admin/content/achievements/import') }}">@csrf
            <div class="mb-3"><label class="form-label">Highlight link <span class="text-muted">(optional - shown as "More on Instagram")</span></label>
                <input class="form-control" name="highlight_url" placeholder="https://www.instagram.com/stories/highlights/..." value="{{ old('highlight_url') }}">@error('highlight_url')<div class="text-danger small">{{ $message }}</div>@enderror</div>
            <div class="mb-3"><label class="form-label">Pasted response <span class="text-danger">*</span></label>
                <textarea class="form-control font-monospace" name="payload" rows="10" required placeholder='{"code":200,"msg":"SUCCESS","result":{...}}'>{{ old('payload') }}</textarea>@error('payload')<div class="text-danger small">{{ $message }}</div>@enderror</div>
            <button class="btn btn-success mb-0"><i class="bi bi-cloud-arrow-down me-1"></i>Import now</button>
        </form>
    </div></div>
</div>
@endsection
