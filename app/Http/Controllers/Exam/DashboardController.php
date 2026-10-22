<?php

namespace App\Http\Controllers\Exam;

use App\Http\Controllers\Concerns\ResolvesExam;
use App\Http\Controllers\Controller;
use App\Services\ExamDashboard;
use Illuminate\Http\Request;

/**
 * Home page of an exam type that has no admin module of its own (an exam added in General > Exam Types).
 * admin/exam/{exam}
 */
class DashboardController extends Controller
{
    use ResolvesExam;

    public function index(Request $request, $examSlug)
    {
        $exam = $this->exam($examSlug);

        return view('admin.exam.dashboard', [
            'exam' => $exam, 'layout' => $exam->layout(), 'dash' => (new ExamDashboard($exam))->data(),
            'isAdmin' => $request->user()->type === 'Admin',
        ]);
    }
}
