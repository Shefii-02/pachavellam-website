{{-- Footer of the website: link columns (managed under Website content > Footer links, with sensible defaults) --}}
@php
    $custom = \App\Models\ContentItem::ofKind('footer_link')->live()->orderBy('position')->orderBy('id')->get()->groupBy(fn ($l) => $l->type ?: 'Links');
    $goals = \App\Models\CompetitiveExam::where('status', 1)->orderBy('position')->get();
    $columns = $custom->map(fn ($rows) => $rows->map(fn ($r) => [$r->title, $r->link_url])->all())->all();
    if (!$columns) {
        $columns = [
            'Company' => [['About us', url('about-us')], ['Contact us', url('contact-us')], ['FAQ', url('faq')]],
            'Free for everyone' => [['Free classes', url('free-classes')], ['Free articles', url('articles')], ['Announcements', url('announcements')]],
            'Legal' => [['Terms & conditions', url('info/terms-and-conditions')], ['Privacy policy', url('info/privacy-policy')]],
        ];
    }
    $columns = ['Popular goals' => $goals->map(fn ($g) => [$g->short_name ?: $g->name, url($g->slug)])->all()] + $columns;
    $footerPartners = \App\Models\ContentItem::ofKind('partner')->live()->orderBy('position')->orderBy('id')->get();
@endphp
<footer id="siteFooter" style="background:#0f1d10;color:#c9d6c2;padding:28px 0 18px;margin-top:28px">
    <style>#siteFooter a{color:#c9d6c2;text-decoration:none;font-size:.86rem;display:block;padding:2px 0} #siteFooter a:hover{color:#fff} #siteFooter h6{color:#fff;font-weight:800;margin-bottom:8px}</style>
    <div class="container">
        <div class="row g-4">
            @foreach ($columns as $title => $links)
                @if (count($links))
                    <div class="col-6 col-md-3"><h6>{{ $title }}</h6>@foreach ($links as [$label, $href])<a href="{{ $href }}">{{ $label }}</a>@endforeach</div>
                @endif
            @endforeach
        </div>
        @if ($footerPartners->count())
            <h6 class="mt-4">Our associate partners</h6>
            <div class="d-flex flex-wrap gap-2">
                @foreach ($footerPartners as $p)
                    <a href="{{ $p->link_url ?: '#' }}" @if ($p->link_url) target="_blank" rel="noopener" @endif title="{{ $p->title }}" style="display:flex;align-items:center;gap:6px;background:#fff;border-radius:8px;padding:4px 10px;height:36px;color:#1c2b14 !important;font-weight:700;font-size:.78rem">
                        @if ($p->image)<img src="{{ Storage::url('files/' . $p->image) }}" alt="{{ $p->title }}" style="max-height:26px;max-width:110px;object-fit:contain" loading="lazy">@else{{ $p->title }}@endif
                    </a>
                @endforeach
            </div>
        @endif
        <div class="mt-4 pt-3 small" style="border-top:1px solid rgba(255,255,255,.12)">&copy; {{ date('Y') }} Pachavellam. All rights reserved.</div>
    </div>
</footer>
