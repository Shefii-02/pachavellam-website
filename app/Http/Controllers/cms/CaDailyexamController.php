<?php

namespace App\Http\Controllers\cms;

use App\Models\DailyExam;
use App\Models\DailyExamattempt;
use App\Models\SampleLogin;
use App\Models\DailyExamdetails;
use App\Models\KpscSubject;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Support\Facades\Auth;
use App\Helper\Reply;
use App\Helper;
use App\Models\CaDailyExamAttempt;
use Illuminate\Support\Str;

class CaDailyexamController extends Controller
{
    public function daily_exams()
    {
        $examDays = DailyExam::where('section', 'CA-Daily-Exam')->pluck('level');
       
        return view('cms.ca-exam.daily-exam', compact('examDays'));
    }

    public function daily_exams_store(Request $request)
    {
        $validated = $request->validate([
            'level' => 'required|unique:kpsc_daily_exams,level',
            'exam_started' => 'required',
        ]);

        $new_one = DailyExam::where('exam_date', $request->exam_date)
            ->where('subject', $request->subject)
            ->where('level', $request->level)
            ->where('section', 'CA-Daily-Exam')
            ->first();

        if (!$new_one) {
            $new_one  = new DailyExam();
        }
        $new_one->section = "CA-Daily-Exam";
        $new_one->exam_date = date('Y-m-d', strtotime($request->exam_started));
        $new_one->started_at = date('Y-m-d H:i:s', strtotime($request->exam_started));
        $new_one->examtitle = $request->level;
        $new_one->level = $request->level;
        $new_one->status = 0;

        try {
            $new_one->save();
            if ($request->has('question') && count($request->question) > 0) {

                $new_one = DailyExam::where('exam_date', $request->exam_date)
                    ->where('subject', $request->subject)
                    ->where('level', $request->level)
                    ->where('section', 'CA-Daily-Exam')
                    ->first();
                if (!$new_one) {
                    $new_one  = new DailyExam();
                }
                $new_one->section = "CA-Daily-Exam";
                $new_one->exam_date = date('Y-m-d', strtotime($request->exam_date));
                $new_one->started_at = date('Y-m-d H:i:s', strtotime($request->exam_started));
                $new_one->ended_at = date('Y-m-d H:i:s', strtotime($request->exam_ended));
                $new_one->subject = $request->subject;
                $new_one->level = $request->level;

                try {
                    $new_one->save();

                    for ($i = 0; $i < count($request->question); $i++) {
                        $new_two = new DailyExamdetails();
                        $new_two->exam_id = $new_one->id;
                        $new_two->question = $request->question[$i];
                        $new_two->option_1 = $request->option1[$i];
                        $new_two->option_2 = $request->option2[$i];
                        $new_two->option_3 = $request->option3[$i];
                        $new_two->option_4 = $request->option4[$i];
                        $new_two->currect_ans = $request->answer[$i];
                        $new_two->save();
                    }

                    return redirect()->back()->with('message', 'Data added Successfully');
                } catch (Exception $e) {
                    dd($e);
                }
            } else {
                return redirect(kpsc_cms('ca-daily-exam/edit/' . $new_one->id))
                    ->with('message', 'Successfully Created!');
            }
        } catch (Exception $e) {
            dd($e);
        }
    }
    public function daily_exams_show()
    {
        $level_list = DailyExam::where('section', 'CA-Daily-Exam')->orderBy('level', 'desc')->get();
        $exam_titles = DailyExam::where('section', 'CA-Daily-Exam')->groupBy('examtitle')->get('examtitle');
        $existingExams = DailyExam::where('section', 'CA-Daily-Exam')->orderBy('status', 'asc')->orderBy('level', 'desc')->take(25)->get();
        return view('cms.ca-exam.daily-exam-show', compact('level_list', 'exam_titles', 'existingExams'));
    }


    public function exam_details_update(Request $request)
    {
        $examtitle    = $request->level;
        $subject      = $request->subject;
        $level        = $request->level;

        $new_one = DailyExam::where('id', $request->exam_id)->first() ?? abort(404);


        $new_one->section       = "CA-Daily-Exam";
        $new_one->exam_date     = date('Y-m-d', strtotime($request->exam_started));
        $new_one->started_at    = date('Y-m-d H:i:s', strtotime($request->exam_started));
        $new_one->subject       = $subject;
        $new_one->examtitle     = $examtitle;
        $new_one->level         = $level;
        $new_one->explanation   = $request->explanation ?? null;

        try {
            $new_one->save();
        } catch (Exception $e) {
            dd($e);
        }

        return redirect()->back()->with('message', 'Data Updated Successfully');
    }


    public function daily_exams_date_based(Request $request)
    {

        $date_list  = DailyExam::where('section', 'CA-Daily-Exam')->where('level', $request->date)->get() ?? abort(404);

        return view('cms.ca-exam.daily-exam-show-date_based', compact('date_list'));
    }

    public function daily_exams_edit($id)
    {
        $exam_detail = DailyExam::where('id', $id)->first() ?? abort('404');
        $examDays = DailyExam::where('section', 'CA-Daily-Exam')->pluck('level');
        return view('cms.ca-exam.daily-exam-edit', compact('exam_detail','examDays'));
    }


    public function save_question($id, Request $request)
    {
        $validated = $request->validate([
            'question' => 'required',
            'option1' => 'required',
            'option2' => 'required',
            'option3' => 'required',
            'option4' => 'required',
            'answer' => 'required',
        ]);
        $new_two = new DailyExamdetails();
        $new_two->exam_id = $id;
        $new_two->question = $request->question ?? '';
        $new_two->option_1 = $request->option1 ?? '';
        $new_two->option_2 = $request->option2 ?? '';
        $new_two->option_3 = $request->option3 ?? '';
        $new_two->option_4 = $request->option4 ?? '';
        $new_two->currect_ans = $request->answer ?? '';
        $new_two->notes       = $request->explanation ?? '';

        $new_two->save();
        return redirect()->back()->with('message', 'Data Saved Successfully');
    }
    public function update_question($id, Request $request)
    {
        $validated = $request->validate([
            'question' => 'required',
            'option1' => 'required',
            'option2' => 'required',
            'option3' => 'required',
            'option4' => 'required',
            'answer' => 'required',
        ]);

        $new_two = DailyExamdetails::where('id', $id)->first() ?? abort(404);
        $new_two->question = $request->question;
        $new_two->option_1 = $request->option1;
        $new_two->option_2 = $request->option2;
        $new_two->option_3 = $request->option3;
        $new_two->option_4 = $request->option4;
        $new_two->currect_ans = $request->answer;
        $new_two->notes       = $request->explanation ?? '';
        $new_two->save();

        return redirect()->back()->with('message', 'Data Updated Successfully');
    }

    public function questionEdit(Request $request)
    {
        $exam_detail = DailyExamdetails::where('id', $request->id)->first() ?? abort(404);

        return view('cms.ca-exam.daily-edit-question', compact('exam_detail'))->render();
    }

    public function daily_exams_update($id, Request $request)
    {
        $new_two = DailyExam::where('id', $request->id)
            ->where('section', 'CA-Daily-Exam')
            ->first() ?? abort(404);

        $new_two->exam_date = date('Y-m-d', strtotime($request->exam_started));
        $new_two->started_at = date('Y-m-d H:i:s', strtotime($request->exam_started));
        $new_two->level = $request->level;
        $new_two->status    = $request->has('status') ? 1 : 0;
        $new_two->save();

        return redirect('admin/kpsc/ca-daily-exam/view')->with('message', 'Data Updated Successfully');
    }


    public function questionDelete(Request $request)
    {
        $deleted = DailyExamdetails::where('id', $request->id)->delete();

        if (!$deleted) {
            return response()->json(['error' => 'Not found'], 404);
        }

        return response()->json(['success' => true]);
    }


    public function daily_exams_delete($id)
    {

        $date_based = DailyExamdetails::where('id', $id)->first();
        $date_based->delete();

        return redirect()->back()->with('message', 'Data deleted Successfully');
    }

    public function daily_exams_delete_all($id)
    {
        $exam = DailyExam::where('id', $id)->first();  
        DailyExamdetails::where('exam_id', $id)->delete();
        CaDailyExamAttempt::where('exam_id', $id)->delete();
        DailyExam::where('id', $id)->delete();

        // DailyExam::leftJoin('kpsc_daily_exams_details', 'kpsc_daily_exams_details.exam_id', 'kpsc_daily_exams.id')->where('kpsc_daily_exams_details.exam_id', $id)->where('kpsc_daily_exams.id', $id)->delete();
        return redirect()->back()->with('message', 'Data deleted Successfully');
    }


    public function clear_leaderboard($id)
    {

        $date_based = CaDailyExamAttempt::where('exam_id', $id)->delete();

        return redirect()->back()->with('message', 'Data deleted Successfully');
    }
}
