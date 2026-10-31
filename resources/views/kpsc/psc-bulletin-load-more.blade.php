@foreach ($pscbulletin as $list)
    @php $when = \Illuminate\Support\Carbon::parse($list->month_year); $show = url('kpsc/psc-bulletin/' . $list->id); @endphp
    <div class="bl-col">
        <div class="sr-card">
            <div class="bd">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="small text-muted">{{ $when->format('M Y') }}</span><span class="sr-chip fr">Issue {{ $list->issue }}</span>
                </div>
                <div class="bl-ttl">PSC Bulletin &middot; {{ $when->format('F Y') }}</div>
                <div class="bl-file">{{ $list->file_name }}</div>
                <div class="d-flex flex-wrap gap-2 mt-2 align-items-center">
                    <a class="sr-go" href="{{ $show }}"><i class="fa fa-book"></i> Read</a>
                    <span class="small text-muted ms-auto"><i class="fa fa-download"></i> {{ number_format((int) $list->download) }}</span>
                </div>
            </div>
        </div>
    </div>
    @include('partials.ad-slot', ['type' => 'web_infeed', 'page' => 'bulletin', 'pos' => $loop->iteration, 'pageNo' => $pscbulletin->currentPage()])
@endforeach
@unless ($pscbulletin->hasMorePages())
    <span class="bl-end" hidden></span>
@endunless
