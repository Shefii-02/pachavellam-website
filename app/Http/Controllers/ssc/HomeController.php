<?php

namespace App\Http\Controllers\ssc;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Services\ExamDashboard;

class HomeController extends Controller
{
    public function dashboard()
    {
        $exam = CompetitiveExam::where('slug', 'ssc')->firstOrFail();

        return view('admin.ssc.dashboard', ['dash' => (new ExamDashboard($exam))->data()]);
    }
}
