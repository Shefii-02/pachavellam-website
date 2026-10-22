<?php

namespace App\Http\Controllers\ktet;

use App\Http\Controllers\Controller;
use App\Models\CompetitiveExam;
use App\Services\ExamDashboard;

class HomeController extends Controller
{
    public function dashboard()
    {
        $exam = CompetitiveExam::where('slug', 'ktet')->firstOrFail();

        return view('admin.ktet.dashboard', ['dash' => (new ExamDashboard($exam))->data()]);
    }
}
