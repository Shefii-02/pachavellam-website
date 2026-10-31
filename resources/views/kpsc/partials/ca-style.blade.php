{{-- Look of the daily current affairs pages (year / month / day / questions): put inside @section('styles') --}}
<style>
    .ca-page { background: #f4f7f1 url(https://www.pachavellam.com/assets/images/bg-pacha-area.jpg) center / cover fixed no-repeat; min-height: 100vh; padding: 18px 0 40px; }
    .ca-hero { background: linear-gradient(135deg, #3f7d1d, #77ab09); color: #fff; border-radius: 16px; padding: 20px; box-shadow: 0 6px 18px rgba(63, 125, 29, .25); }
    .ca-crumbs { display: flex; flex-wrap: wrap; gap: 4px 6px; font-size: 12px; margin-bottom: 8px; opacity: .9; }
    .ca-crumbs a { color: #fff !important; text-decoration: underline; } .ca-crumbs i { font-style: normal; opacity: .7; }
    .ca-hero h1 { font-size: 22px; font-weight: 700; margin: 0 0 6px; color: #fff; text-transform: capitalize; }
    .ca-hero p { margin: 0; font-size: 14px; line-height: 1.8; opacity: .95; color: #fff; }
    .ca-share { display: flex; align-items: center; gap: 10px; margin-top: 14px; flex-wrap: wrap; }
    .ca-share span { font-size: 13px; opacity: .9; }
    .ca-share a { width: 36px; height: 36px; border-radius: 50%; background: #fff; display: inline-flex; align-items: center; justify-content: center; font-size: 18px; text-decoration: none; }

    .ca-grid { display: flex; flex-wrap: wrap; margin: 16px -6px 0; }
    .ca-grid > .col-lg-12 { width: 100%; padding: 0 6px; }   /* the ad block from ads_space_sport() */
    .ca-cell { width: 50%; padding: 6px; }
    @media (min-width: 768px) { .ca-cell { width: 25%; } }
    .ca-tile { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 8px; min-height: 112px; height: 100%; padding: 14px 8px; text-align: center; background: var(--pv-surface, #fff); border: 1px solid var(--pv-border, #e5e7eb); border-radius: 16px; box-shadow: 0 2px 10px rgba(0, 0, 0, .06); color: var(--pv-text, #16202e) !important; text-decoration: none !important; transition: transform .15s, box-shadow .15s; }
    .ca-tile:hover { transform: translateY(-2px); box-shadow: 0 8px 20px rgba(0, 0, 0, .12); }
    .ca-ic { width: 42px; height: 42px; border-radius: 50%; background: #eef6df; color: #3f7d1d; display: flex; align-items: center; justify-content: center; font-size: 18px; }
    .ca-ic .fa { margin: 0; }
    .ca-tl { font-weight: 800; font-size: 1.05rem; line-height: 1.2; text-transform: capitalize; }

    .ca-list { margin-top: 16px; display: grid; gap: 10px; }
    .ca-row { display: flex; align-items: center; gap: 12px; padding: 12px; background: var(--pv-surface, #fff); border: 1px solid var(--pv-border, #e5e7eb); border-radius: 14px; box-shadow: 0 2px 10px rgba(0, 0, 0, .05); }
    .ca-row .ca-ic.vid { background: #fde8e8; color: #a4242a; }
    .ca-row .t { flex: 1; min-width: 0; font-weight: 700; font-size: .92rem; line-height: 1.3; color: var(--pv-text, #16202e); }
    .ca-go { flex: none; border-radius: 20px; padding: 5px 14px; font-size: .78rem; font-weight: 800; background: var(--pv-green, #0a8f5b); color: var(--pv-on-brand, #fff) !important; text-decoration: none !important; }
    .ca-sec { margin: 22px 0 0; font-size: 1rem; font-weight: 800; color: var(--pv-text, #16202e); }

    .ca-q { background: var(--pv-surface, #fff); border: 1px solid var(--pv-border, #e5e7eb); border-radius: 16px; padding: 14px; box-shadow: 0 2px 10px rgba(0, 0, 0, .05); }
    .ca-q .qn { display: inline-block; font-size: .7rem; font-weight: 800; background: #e7f6ef; color: #0a6b46; border-radius: 10px; padding: 2px 10px; margin-bottom: 8px; }
    .ca-q .qt { font-weight: 700; line-height: 1.6; color: var(--pv-text, #16202e); }
    .ca-q details { margin-top: 10px; }
    .ca-q summary { display: inline-block; list-style: none; cursor: pointer; border-radius: 20px; padding: 5px 14px; font-size: .78rem; font-weight: 800; background: var(--pv-green, #0a8f5b); color: var(--pv-on-brand, #fff); }
    .ca-q summary::-webkit-details-marker { display: none; }
    .ca-q details[open] summary { background: var(--pv-surface, #fff); border: 1px solid var(--pv-border, #e5e7eb); color: var(--pv-muted, #667); }
    .ca-ans { margin-top: 10px; padding: 10px 12px; border-radius: 12px; background: #e7f6ef; color: #0a4a31; line-height: 1.6; }
    .ca-ans b { display: block; font-size: 1rem; }
    .ca-note { margin-top: 8px; font-size: .84rem; color: var(--pv-muted, #667); line-height: 1.6; }
    .ca-empty { text-align: center; color: var(--pv-muted, #667); padding: 30px 10px; border: 1px dashed var(--pv-border, #e5e7eb); border-radius: 14px; margin-top: 16px; }
</style>
