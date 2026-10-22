<?php

namespace App\Http\Controllers\rrb;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Services\ExamDashboard;

class HomeController extends Controller
{
    public function dashboard()
    {
        $exam = CompetitiveExam::where('slug', 'rrb')->firstOrFail();

        return view('admin.rrb.dashboard', ['dash' => (new ExamDashboard($exam))->data()]);
    }
}
