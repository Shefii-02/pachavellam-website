@foreach ($pscbulletin as $list)
    @php $when = \Illuminate\Support\Carbon::parse($list->month_year); $file = Storage::url('bullet-in/' . $list->file_name); @endphp
    <div class="bl-col">
        <a href="{{ $file }}" data-id="{{ $list->id }}" download="{{ $list->file_name }}" class="bl-card dwn_c">
            <div class="bl-date">
                <span class="bl-month">{{ $when->format('M') }}</span>
                <span class="bl-year">{{ $when->format('Y') }}</span>
            </div>
            <div class="bl-body">
                <h6 class="bl-title">PSC Bulletin &middot; {{ $when->format('F Y') }}</h6>
                <div class="bl-file">{{ $list->file_name }}</div>
                <div class="bl-meta">
                    <span class="bl-chip">Issue {{ $list->issue }}</span>
                    <span class="bl-count"><i class="fa fa-download"></i> {{ number_format((int) $list->download) }}</span>
                </div>
            </div>
            <span class="bl-dl" aria-label="Download"><i class="fa fa-download"></i></span>
        </a>
    </div>
    @include('partials.ad-slot', ['type' => 'web_infeed', 'page' => 'bulletin', 'pos' => $loop->iteration, 'pageNo' => $pscbulletin->currentPage()])
@endforeach
@unless ($pscbulletin->hasMorePages())
    <span class="bl-end" hidden></span>
@endunless
