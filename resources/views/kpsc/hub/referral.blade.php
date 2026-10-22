{{-- Refer & earn card of My account. Needs $referral (ReferralService::forStudent). --}}
@if ($referral['enabled'])
@php $rs = $referral['settings']; @endphp
<div class="pd-card" id="pf-referral">
    <h2><i class="bi bi-gift" style="color:#d97706"></i> Refer &amp; earn</h2>
    <div class="small mb-3" style="color:var(--pv-muted)">
        Share your link. When a friend joins {{ $rs['qualify'] === 'signup' ? '' : 'and finishes a first test' }}, you get <b style="color:var(--pv-text)">{{ $rs['referrer_coins'] }} coins</b>@if ($rs['referred_coins']) and your friend gets <b style="color:var(--pv-text)">{{ $rs['referred_coins'] }} coins</b>@endif.
        Works on the website, Android and iPhone.
    </div>

    <div class="d-flex gap-2 align-items-center flex-wrap mb-2">
        <div style="flex:1 1 220px;min-width:0;border:1px dashed var(--pv-border);border-radius:12px;padding:8px 12px">
            <div class="small" style="color:var(--pv-muted)">Your code</div>
            <div style="font-weight:800;letter-spacing:2px;font-size:1.15rem" id="refCode">{{ $referral['code'] }}</div>
        </div>
        <button type="button" class="hb-btn ghost" id="refCopy" data-text="{{ $referral['link'] }}"><i class="bi bi-clipboard"></i> Copy link</button>
        <button type="button" class="hb-btn" id="refShare" data-text="{{ $referral['text'] }}" data-url="{{ $referral['link'] }}"><i class="bi bi-share"></i> Share</button>
        <a class="hb-btn ghost" target="_blank" rel="noopener" href="https://wa.me/?text={{ rawurlencode($referral['text']) }}" style="color:#128c4a !important"><i class="bi bi-whatsapp"></i></a>
    </div>
    <div class="small" style="color:var(--pv-muted);word-break:break-all">{{ $referral['link'] }}</div>

    <div class="pd-grid mt-3">
        <div class="pd-kpi"><b>{{ $referral['joined'] }}</b><small>Friends joined</small></div>
        <div class="pd-kpi"><b>{{ $referral['rewarded'] }}</b><small>Rewarded</small></div>
        <div class="pd-kpi"><b>{{ $referral['pending'] }}</b><small>Waiting for a first test</small></div>
        <div class="pd-kpi"><b>{{ number_format($referral['earned']) }}</b><small>Coins earned</small></div>
    </div>

    @if ($referral['invitedBy'])
        <div class="small mt-3"><i class="bi bi-person-heart"></i> You joined through <b>{{ $referral['invitedBy']->name }}</b>@if ($referral['invitedBy']->status === 'rewarded' && $referral['invitedBy']->coins) &mdash; you got {{ $referral['invitedBy']->coins }} coins @elseif ($rs['referred_coins']) &mdash; your {{ $rs['referred_coins'] }} coins come after your first test @endif.</div>
    @elseif ($referral['canApply'])
        <form method="POST" action="{{ url('my-account/referral') }}" class="d-flex gap-2 mt-3">@csrf
            <input name="code" maxlength="12" required placeholder="Have a friend's code?" style="flex:1;min-width:0;border:1px solid var(--pv-border);background:var(--pv-surface);color:var(--pv-text);border-radius:12px;padding:8px 12px;text-transform:uppercase">
            <button class="hb-btn">Apply</button>
        </form>
    @endif

    @if ($referral['rows']->count())
        <div class="mt-3 fw-bold small">My friends</div>
        <table class="pd-tbl"><tbody>
            @foreach ($referral['rows'] as $f)
                <tr>
                    <td>{{ $f->name }}<div class="small" style="color:var(--pv-muted)">{{ \Illuminate\Support\Carbon::parse($f->at)->format('d M Y') }} · {{ ['web' => 'Website', 'android' => 'Android', 'ios' => 'iPhone'][$f->platform] ?? ucfirst($f->platform) }}</div></td>
                    <td class="text-end">@if ($f->status === 'rewarded')<span style="color:#0a8f5b;font-weight:700">+{{ $f->coins }} <i class="bi bi-coin" style="color:#d97706"></i></span>@else<span class="small" style="color:var(--pv-muted)">Waiting for first test</span>@endif</td>
                </tr>
            @endforeach
        </tbody></table>
    @endif
</div>
<script>
(function () {
    var c = document.getElementById('refCopy'), s = document.getElementById('refShare');
    if (c) c.onclick = function () { var t = this.dataset.text; (navigator.clipboard ? navigator.clipboard.writeText(t) : Promise.reject()).then(function () { c.innerHTML = '<i class="bi bi-check2"></i> Copied'; setTimeout(function () { c.innerHTML = '<i class="bi bi-clipboard"></i> Copy link'; }, 1800); }).catch(function () { prompt('Copy your link', t); }); };
    if (s) s.onclick = function () { if (navigator.share) navigator.share({ title: 'Pachavellam', text: this.dataset.text, url: this.dataset.url }).catch(function () {}); else if (c) c.click(); };
})();
</script>
@endif
