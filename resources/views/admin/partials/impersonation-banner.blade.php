@if (session('impersonator_id'))
    <div style="position:sticky;top:0;z-index:2000;background:#fd7e14;color:#fff;padding:7px 14px;display:flex;gap:12px;align-items:center;justify-content:center;flex-wrap:wrap;font-size:.9rem">
        <span><i class="bi bi-person-badge me-1"></i>You are viewing the panel as <strong>{{ auth()->user()->name }}</strong> (staff). What you do is saved under their name and marked as done by you.</span>
        <form action="{{ url('admin/impersonate/stop') }}" method="POST" class="m-0">@csrf
            <button class="btn btn-sm btn-light py-0" style="font-weight:600">Return to my account</button></form>
    </div>
@endif
