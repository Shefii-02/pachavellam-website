<?php

namespace App\Http\Controllers\ugc_net;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Services\ExamDashboard;

class HomeController extends Controller
{
    public function dashboard()
    {
        $exam = CompetitiveExam::where('slug', 'ugc-net')->firstOrFail();

        return view('admin.ugc_net.dashboard', ['dash' => (new ExamDashboard($exam))->data()]);
    }
}
