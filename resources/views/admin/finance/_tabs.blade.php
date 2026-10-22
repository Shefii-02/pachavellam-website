@php $exam = request('exam'); $q = $exam ? '?exam=' . $exam : ''; @endphp
<ul class="nav nav-pills mb-3 gap-1">
    <li class="nav-item"><a class="nav-link py-1 {{ request()->is('admin/revenue*') ? 'active' : '' }}" href="{{ url('admin/revenue' . $q) }}"><i class="bi bi-graph-up me-1"></i>Revenue</a></li>
    <li class="nav-item"><a class="nav-link py-1 {{ request()->is('admin/admissions*') ? 'active' : '' }}" href="{{ url('admin/admissions' . $q) }}"><i class="bi bi-person-check me-1"></i>Admissions</a></li>
    <li class="nav-item"><a class="nav-link py-1 {{ request()->is('admin/payment-links*') ? 'active' : '' }}" href="{{ url('admin/payment-links' . $q) }}"><i class="bi bi-link-45deg me-1"></i>Payment links</a></li>
    <li class="nav-item"><a class="nav-link py-1 {{ request()->is('admin/transactions*') ? 'active' : '' }}" href="{{ url('admin/transactions' . $q) }}"><i class="bi bi-receipt me-1"></i>Transactions</a></li>
</ul>
