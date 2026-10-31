@foreach ($pscbulletin as $list)
    @php $when = \Illuminate\Support\Carbon::parse($list->month_year); $file = Storage::url('bullet-in/' . $list->file_name); @endphp
    <div class="bl-col">
        <div class="bl-card">
            <div class="bl-date">
                <span class="bl-month">{{ $when->format('M') }}</span>
                <span class="bl-year">{{ $when->format('Y') }}</span>
            </div>
            <div class="bl-body">
                <h6 class="bl-title"><a href="{{ url('kpsc/psc-bulletin/' . $list->id) }}" class="bl-open">PSC Bulletin &middot; {{ $when->format('F Y') }}</a></h6>
                <div class="bl-file">{{ $list->file_name }}</div>
                <div class="bl-meta">
                    <span class="bl-chip">Issue {{ $list->issue }}</span>
                    <span class="bl-count"><i class="fa fa-download"></i> {{ number_format((int) $list->download) }}</span>
                    <span class="bl-read">Read</span>
                </div>
            </div>
            <a href="{{ $file }}" data-id="{{ $list->id }}" download="{{ $list->file_name }}" class="bl-dl dwn_c" aria-label="Download" title="Download"><i class="fa fa-download"></i></a>
        </div>
    </div>
    @include('partials.ad-slot', ['type' => 'web_infeed', 'page' => 'bulletin', 'pos' => $loop->iteration, 'pageNo' => $pscbulletin->currentPage()])
@endforeach
@unless ($pscbulletin->hasMorePages())
    <span class="bl-end" hidden></span>
@endunless
