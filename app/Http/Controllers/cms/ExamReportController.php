<?php

namespace App\Http\Controllers\cms;

use App\Http\Controllers\Controller;
use App\Models\ExamReport;
use App\Models\DailyExam;
use Illuminate\Http\Request;

/** Students report an error on an exam (kpsc/exam-report); admin reads and resolves them. */
class ExamReportController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'exam_id' => 'required|integer',
            'type' => 'required|in:' . implode(',', ExamReport::TYPES),
            'message' => 'required|string|min:20|max:2000',
            'question_no' => 'nullable|string|max:20',
        ]);
        abort_unless(DailyExam::whereKey($data['exam_id'])->exists(), 404);
        ExamReport::create($data + ['user_id' => auth()->id()]);

        return response()->json(['ok' => true]);
    }

    public function index(Request $request)
    {
        $status = $request->query('status', 'open');
        $reports = ExamReport::with(['user:id,name,mobile', 'exam:id,examtitle,exam_date'])->where('status', $status)->latest('id')->paginate(30)->withQueryString();

        return view('cms.exam-reports', compact('reports', 'status'));
    }

    public function resolve($id)
    {
        $r = ExamReport::findOrFail($id);
        $r->update(['status' => $r->status === 'open' ? 'resolved' : 'open']);

        return back();
    }
}
