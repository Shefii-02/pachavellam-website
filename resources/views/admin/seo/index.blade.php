@extends('admin.main-layout')
@section('main')
<div class="page-content-wrapper border">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
        <div>
            <h3 class="mb-0"><i class="bi bi-search me-1"></i>SEO - title, description &amp; words</h3>
            <div class="small text-muted">What Google and WhatsApp / Facebook show for each page. Leave a box empty to keep the automatic text. Articles, free classes and courses use their own title, text and picture automatically.</div>
        </div>
        <form class="d-flex gap-2" method="GET"><input class="form-control form-control-sm" name="q" value="{{ $q }}" placeholder="Search page…"><button class="btn btn-sm btn-primary mb-0">Search</button></form>
    </div>
    @if (session('message'))<div class="alert alert-success py-2">{{ session('message') }}</div>@endif
    @if ($errors->any())<div class="alert alert-danger py-2">{{ $errors->first() }}</div>@endif

    @foreach ($pages->groupBy('group') as $group => $items)
        <h6 class="text-uppercase text-muted mt-4 mb-2">{{ $group }}</h6>
        <div class="accordion" id="acc{{ Str::slug($group) }}">
        @foreach ($items as $p)
            @php $r = $p->row; $id = Str::slug($p->key); $filled = $r && ($r->title || $r->description || $r->keywords || $r->og_image); @endphp
            <div class="accordion-item bg-transparent" id="{{ $id }}">
                <h2 class="accordion-header"><button class="accordion-button collapsed bg-transparent" type="button" data-bs-toggle="collapse" data-bs-target="#c{{ $id }}">
                    <span class="fw-semibold me-2">{{ $p->label }}</span><code class="small me-2">{{ $p->path }}</code>
                    @if ($filled)<span class="badge bg-success">Custom</span>@else<span class="badge bg-light text-muted border">Automatic</span>@endif
                </button></h2>
                <div id="c{{ $id }}" class="accordion-collapse collapse {{ request()->query('open') === $p->key ? 'show' : '' }}" data-bs-parent="#acc{{ Str::slug($group) }}">
                    <div class="accordion-body">
                        <form method="POST" action="{{ url('admin/seo') }}" enctype="multipart/form-data" class="row g-3" data-seo>@csrf
                            <input type="hidden" name="page_key" value="{{ $p->key }}">
                            <div class="col-lg-7">
                                <label class="form-label">Page title <span class="text-muted small">(best up to 60 characters)</span></label>
                                <input class="form-control" name="title" maxlength="160" value="{{ $r->title ?? '' }}" placeholder="{{ $p->auto_title }}" data-f="title">
                                <div class="form-text"><span data-c="title">0</span> characters</div>
                                <label class="form-label mt-2">Description <span class="text-muted small">(best up to 160 characters)</span></label>
                                <textarea class="form-control" name="description" rows="3" maxlength="400" placeholder="{{ $p->auto_description }}" data-f="desc">{{ $r->description ?? '' }}</textarea>
                                <div class="form-text"><span data-c="desc">0</span> characters</div>
                                <label class="form-label mt-2">Keywords / words <span class="text-muted small">(separate with commas)</span></label>
                                <input class="form-control" name="keywords" maxlength="400" value="{{ $r->keywords ?? '' }}" placeholder="{{ $siteKeywords }}">
                                <div class="row g-3 mt-0">
                                    <div class="col-md-6"><label class="form-label mt-2">Search engines</label>
                                        <select class="form-select" name="robots">
                                            @foreach (['' => 'Automatic (index, follow)', 'index,follow' => 'Show in search', 'noindex,follow' => 'Hide this page from search', 'noindex,nofollow' => 'Hide page and its links', 'index,nofollow' => 'Show page, ignore its links'] as $v => $l)
                                                <option value="{{ $v }}" {{ ($r->robots ?? '') === $v ? 'selected' : '' }}>{{ $l }}</option>
                                            @endforeach
                                        </select></div>
                                    <div class="col-md-6"><label class="form-label mt-2">Share picture <span class="text-muted small">(1200 x 630)</span></label>
                                        <input type="file" class="form-control" name="og_image" accept="image/*">
                                        @if ($r && $r->og_image)<div class="form-check mt-1"><input class="form-check-input" type="checkbox" name="remove_image" value="1" id="ri{{ $id }}"><label class="form-check-label small" for="ri{{ $id }}">Remove current picture</label></div>@endif</div>
                                </div>
                            </div>
                            <div class="col-lg-5">
                                <div class="small text-muted mb-1">How it looks in Google</div>
                                <div class="border rounded-3 p-3 mb-3" style="background:#fff;color:#202124">
                                    <div class="small" style="color:#4d5156">pachavellam.com{{ $p->path === '/' ? '' : ' › ' . ltrim($p->path, '/') }}</div>
                                    <div data-p="title" style="color:#1a0dab;font-size:1.1rem;line-height:1.3"></div>
                                    <div data-p="desc" style="color:#4d5156;font-size:.85rem"></div>
                                </div>
                                @if ($r && $r->og_image)<div class="small text-muted mb-1">Share picture now</div><img src="{{ Storage::url('files/' . $r->og_image) }}" class="rounded-3 mb-3" style="max-width:100%;max-height:150px" alt="">@endif
                                <button class="btn btn-success mb-0"><i class="bi bi-check me-1"></i>Save</button>
                                @if ($filled || ($r && $r->robots))
                                    <button type="submit" form="reset{{ $id }}" class="btn btn-outline-secondary mb-0 ms-1" onclick="return confirm('Go back to the automatic text for this page?')">Use automatic text</button>
                                @endif
                                @if ($r)<div class="small text-muted mt-2">Last saved {{ \Illuminate\Support\Carbon::parse($r->updated_at)->diffForHumans() }}</div>@endif
                            </div>
                        </form>
                        <form id="reset{{ $id }}" method="POST" action="{{ url('admin/seo/' . $p->key . '/reset') }}">@csrf</form>
                        <template data-auto-title="{{ $p->auto_title }}" data-auto-desc="{{ $p->auto_description }}"></template>
                    </div>
                </div>
            </div>
        @endforeach
        </div>
    @endforeach
    @if (!$pages->count())<div class="text-muted text-center py-5">No page matches.</div>@endif
</div>
<script>
    // character counters and the Google preview (typed text, or the automatic text while the box is empty)
    document.querySelectorAll('form[data-seo]').forEach(function (f) {
        var t = f.querySelector('[data-f=title]'), d = f.querySelector('[data-f=desc]'), tpl = f.parentNode.querySelector('template');
        function upd() {
            var title = t.value.trim() || tpl.dataset.autoTitle, desc = d.value.trim() || tpl.dataset.autoDesc;
            f.querySelector('[data-c=title]').textContent = t.value.length; f.querySelector('[data-c=desc]').textContent = d.value.length;
            f.querySelector('[data-p=title]').textContent = title.length > 62 ? title.slice(0, 60) + '…' : title;
            f.querySelector('[data-p=desc]').textContent = desc.length > 160 ? desc.slice(0, 158) + '…' : desc;
            f.querySelector('[data-c=title]').style.color = t.value.length > 60 ? '#c00' : ''; f.querySelector('[data-c=desc]').style.color = d.value.length > 160 ? '#c00' : '';
        }
        t.addEventListener('input', upd); d.addEventListener('input', upd); upd();
    });
    // jump to the page just saved
    if (location.hash) { var e = document.querySelector(location.hash + ' .accordion-collapse'); if (e) e.classList.add('show'); }
</script>
@endsection
