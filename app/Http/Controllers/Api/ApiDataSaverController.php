<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\{Bulletin, CaDailyExamAttempt, ModelExamAttempt, DailyExam, DailyExamattempt, Capsule, CapsuleComment, CapsuleLike, DailyExamdetails};
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Exception;

class ApiDataSaverController extends Controller
{
    public function BulletInDownload($id)
    {
        $bulletin = Bulletin::find($id);

        if (!$bulletin) {
            return response()->json(['error' => 'Bulletin not found'], 404);
        }
        $bulletin->download++;
        $bulletin->save();
        return response()->json(['message' => 'Download count increased successfully', 'bulletin' => $bulletin], 200);
    }

    public function ModelExamUploadMark(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'exam_id' => 'required|integer',
            'user_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => 'Validation failed', 'errors' => $validator->errors()], 400);
        }

        $exam_details = DailyExam::where('id', $request->exam_id)->first();
        date_default_timezone_set('Asia/Kolkata');
        try {
            $new_one = ModelExamAttempt::where('exam_id', $request->exam_id)->where('user_id', $request->user_id)->first();
            if (!$new_one) {
                $new_one = new ModelExamAttempt();
                $new_one->exam_id       = $request->exam_id;
                $new_one->user_id       = $request->user_id;
                $new_one->mobile_no     = null;
                $new_one->ip_address    = $request->ip();
                $new_one->right_answer  = $request->right_answer ?? 0;
                $new_one->wrong_answer  = $request->wrong_answer ?? 0;
                $new_one->total_mark    = $new_one->right_answer - ($new_one->wrong_answer / 3);
                $new_one->answer_uploaded = Carbon::now()->format('Y-m-d H:i:s');
                $new_one->save();

                $result['right_answer'] = $request->right_answer;
                $result['wrong_answer'] = $request->wrong_answer;
                $result['your_score']   = $new_one->total_mark;
                if ($exam_details->ended_at > date('Y-m-d H:i:s')) {
                    $result['leaderboard_show'] = date('Y-m-d h:i a', strtotime($exam_details->ended_at));
                } else {
                    $result['leaderboard_show'] = " Already published please check your rank.";
                }

                return response()->json(['message' => 'Your Mark Added Successfuly', 'data' => $result], 200);
            } else {

                if ($exam_details->ended_at > date('Y-m-d H:i:s')) {
                    $result['leaderboard_show'] = date('Y-m-d h:i a', strtotime($exam_details->ended_at));
                } else {
                    $result['leaderboard_show'] = "Already published please check your rank.";
                }
                return response()->json(['message' => 'Your Mark Already Added', 'data' => $result], 400);
            }
        } catch (Exception $e) {
            $result['leaderboard_show'] = "Report this issue to Pachavellam Admin";

            return response()->json(['message' => $e->getMessage(), 'data' => $result], 401);
        }
    }

    public function DailyExamSingleStore(Request $request)
    {


        $validator = Validator::make($request->all(), [
            'exam_id' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => 'Validation failed', 'errors' => $validator->errors()], 400);
        }


        date_default_timezone_set('Asia/Kolkata');
        $new_one = DailyExamattempt::where('user_id', $request->user_id)
            ->where('exam_id', $request->exam_id)
            ->first();
        try {

            if (!$new_one) {
                $new_one                  = new DailyExamattempt();
                $new_one->user_id         = $request->user_id;
                $new_one->exam_id         = $request->exam_id;
                $new_one->right           = $request->correct;
                $new_one->wrong           = $request->incorrect;
                $new_one->skipped         = $request->unanswered;
                $new_one->attend_ended_at = date('Y-m-d H:i:s');
                $new_one->attempt_time    =    null;
                $new_one->total_mark      = ($request->correct - ($request->incorrect * 0.333));
                $new_one->summary         = $request->summary;
                $new_one->status          = "2";
                $new_one->save();

                return response()->json(['message' => 'Your Mark Added Successfuly', 'data' => $new_one], 200);
            } else {
                return response()->json(['message' => 'Your Mark Already Added', 'data' => $new_one], 400);
            }
        } catch (Exception $e) {
            $result['leaderboard_show'] = "Report this issue to Pachavellam Admin";

            return response()->json(['message' => $e->getMessage(), 'data' => $result], 401);
        }
    }

    public function FeedCommentStore(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'user_id' => 'required',
            'comment' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => 'Validation failed', 'errors' => $validator->errors()], 400);
        }


        date_default_timezone_set('Asia/Kolkata');


        try {
            $new_one = new CapsuleComment();
            $new_one->cap_id = $request->post_id;
            $new_one->ip_adress = $request->ip();
            $new_one->comment = $request->comment;
            $new_one->status = 1;
            $new_one->user_id = $request->user_id;
            $new_one->save();
            return response()->json(['message' => 'Your Comment Added Successfuly', 'data' => $new_one], 200);
        } catch (Exception $e) {
            $result['leaderboard_show'] = "Report this issue to Pachavellam Admin";

            return response()->json(['message' => $e->getMessage(), 'data' => $result], 401);
        }
    }

    public function FeedAddLike(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'post_id' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => 'Validation failed', 'errors' => $validator->errors()], 400);
        }

        date_default_timezone_set('Asia/Kolkata');

        $new_one = CapsuleLike::where('user_id', $request->user_id)
            ->where('cap_id', $request->post_id)
            ->first();
        try {

            if (!$new_one) {
                $new_one = new CapsuleLike();
                $new_one->cap_id = $request->post_id;
                $new_one->ip_adress = $request->ip();
                $new_one->status = 1;
                $new_one->user_id = $request->user_id;
                $new_one->save();
                return response()->json(['message' => 'Your Liked this post', 'data' => $new_one], 200);
            } else {
                return response()->json(['message' => 'Your Already Liked', 'data' => $new_one], 400);
            }
        } catch (Exception $e) {
            $result['leaderboard_show'] = "Report this issue to Pachavellam Admin";

            return response()->json(['message' => $e->getMessage(), 'data' => $result], 401);
        }
    }


    public function FeedReportRequest(Request $request)
    {

        try {

            \Log::info($request->all());

            return response()->json(['message' => 'Your report section saved'], 200);
        } catch (Exception $e) {
            $result['leaderboard_show'] = "Reporting this issue Failed";

            return response()->json(['message' => $e->getMessage()], 401);
        }
    }

    public function FeedReportCommentRequest(Request $request)
    {

        try {

            \Log::info($request->all());

            return response()->json(['message' => 'Your report section saved'], 200);
        } catch (Exception $e) {
            $result['leaderboard_show'] = "Reporting this issue Failed";

            return response()->json(['message' => $e->getMessage()], 401);
        }
    }


    public function ReportMessageRequest(Request $request)
    {

        try {

            \Log::info($request->all());

            return response()->json(['message' => 'Your report section saved'], 200);
        } catch (Exception $e) {
            $result['leaderboard_show'] = "Reporting this issue Failed";

            return response()->json(['message' => $e->getMessage()], 401);
        }
    }


    public function CaDailyExamStore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'exam_id' => 'required',
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['msg' => 'Validation failed', 'errors' => $validator->errors()], 400);
        }

        $totalQstn = DailyExamdetails::where('exam_id', $request->exam_id)->count();
        $star = intval(($request->correct / $totalQstn) * 3); // Convert to integer

        date_default_timezone_set('Asia/Kolkata');

        try {
            $new_one = new CaDailyExamAttempt();
            $new_one->user_id         = $request->user_id;
            $new_one->exam_id         = $request->exam_id;
            $new_one->right           = $request->correct;
            $new_one->wrong           = $request->incorrect;
            $new_one->skipped         = $request->unanswered;
            $new_one->attend_ended_at = date('Y-m-d H:i:s');
            $new_one->total           = ($request->correct - ($request->incorrect * 0.333));
            $new_one->star            = $star; // Now integer
            $new_one->summary         = $request->summary ?? '';
            $new_one->status          = "1";
            $new_one->save();

            return response()->json(['message' => 'Your Mark Added Successfully', 'data' => $new_one], 200);
        } catch (Exception $e) {
            $result['leaderboard_show'] = "Report this issue to Pachavellam Admin";

            return response()->json(['message' => $e->getMessage(), 'data' => $result], 401);
        }
    }
}
