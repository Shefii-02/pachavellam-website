<?php

namespace App\Http\Controllers\cms;

use App\Http\Controllers\Controller;
use App\Services\KpscDashboard;

/** admin/kpsc: the KPSC dashboard. */
class DashboardController extends Controller
{
    public function dashboard()
    {
        return view('cms.index', ['dash' => (new KpscDashboard())->data()]);
    }
}
