<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use App\Models\{Bulletin, CaDailyExamAttempt, Capsule, DailyExam, DailyExamdetails, DailyExamattempt, ModelExamDetails, ModelExamAttempt, CurrentAffairs};
use App\Models\{Banner, SpecialTab, Notification, KpscSubject, Syllabus, PrevQuestion, Pscresults, Pscnews, Timetable, Material};
use Exception;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Storage;
use Hash;
use DB;
use Carbon\Carbon;
use App\Http\Resources\UserResources;

use Illuminate\Support\Str;
use App\Http\Resources\{BulletinResources, FeedResources, DailyExamResources, DailyExamDetailsResources, DailyExamLeaderboardResources};
use App\Http\Resources\{ModelExamResources, ModelExamDetailsResources, ModelExamLeaderboardResources, BannerResources, PrevQyestionPapperResources};
use App\Http\Resources\{ResultResources, SyllabusResources, SubjectCategoryResources, CaDailyExamListResources, CADailyExamLeaderboardResources};

class ApiCollectionController extends Controller

{
    use \App\Http\Controllers\Api\Concerns\GuardsSeasons;

    public function BulletIn(Request $request)
    {
        $bulletin = Bulletin::orderBy('month_year', 'DESC')->orderBy('issue', 'DESC')->get();
        return response()->json(['data' => BulletinResources::collection($bulletin), 'status' => 200]);
    }

    public function FeedList(Request $request)
    {
        $user_id = $request->user_id;
        // each capsule is shown for the exam types ticked on it; apps that do not say which exam default to KPSC
        $capsule_list = Capsule::forExam($request->input('exam', 'kpsc'))->with('author', 'comments', 'comments.author', 'likes')->orderby('created_at', 'desc')->get();
        return response()->json([
            'data' => FeedResources::collection($capsule_list)->additional(['user_id' => $user_id]),
            'status' => 200
        ]);
    }
    // public function FeedList(Request $request)
    // {
    //     // Retrieve the page number and limit from the request
    //     $page = $request->input('page', 1); // Default to page 1 if not provided
    //     $limit = $request->input('limit', 20); // Default to 20 items per page if not provided

    //     // Fetch paginated results
    //     $capsule_list = Capsule::with('author', 'comments', 'comments.author', 'likes')
    //                           ->orderby('created_at', 'desc')
    //                           ->paginate($limit, ['*'], 'page', $page);

    //     // Return paginated results
    //     return response()->json([
    //         'data' => FeedResources::collection($capsule_list->items()),
    //         'current_page' => $capsule_list->currentPage(),
    //         'total_pages' => $capsule_list->lastPage(),
    //         'status' => 200
    //     ]);
    // }


    public function DailyExams(Request $request)
    {
        $date_list = DailyExam::orderBy('exam_date', 'DESC')->where('section', 'Daily Exam')->get();
        return response()->json(['data' => DailyExamResources::collection($date_list), 'status' => 200]);
    }
    public function DailyExamSingle(Request $request)
    {
        if ($locked = $this->seasonLock($request->exam_id)) {
            return $locked;
        }
        $date_details = DailyExamdetails::where('exam_id', $request->exam_id)->get();
        $exam = DailyExam::where('id', $request->exam_id)->first();

        return response()->json(['data' => DailyExamDetailsResources::collection($date_details), 'exam_ended' => $exam->ended_at, 'status' => 200]);
    }
    public function DailyExamLeaderboard(Request $request)
    {
        $exam_attended = DailyExamattempt::with('user')->where('exam_id', $request->exam_id)->where('attempt_no', 1)->get();
        return response()->json(['data' => DailyExamLeaderboardResources::collection($exam_attended), 'status' => 200]);
    }

    public function ModelExams(Request $request)
    {
        $date_list = DailyExam::with('model_exam_details')->orderBy('exam_date', 'DESC')->where('section', 'Model Exam')->get();
        // return $date_list;
        return response()->json(['data' => ModelExamResources::collection($date_list), 'status' => 200]);
    }
    public function ModelExamSingle(Request $request)
    {
        if ($locked = $this->seasonLock($request->exam_id)) {
            return $locked;
        }
        $date_details = ModelExamDetails::where('exam_id', $request->exam_id)->get();
        return response()->json(['data' => ModelExamDetailsResources::collection($date_details), 'status' => 200]);
    }
    public function ModelExamLeaderboard(Request $request)
    {
        date_default_timezone_set('Asia/Kolkata');
        $exm_details = DailyExam::where('id', $request->exam_id)->first();
        if ($exm_details) {

            if ($exm_details->section == 'Daily Exam') {
                // ->where('attend_ended_at','<=',$exm_details->ended_at)
                $exam_attended = DailyExamattempt::with('user')->where('exam_id', $request->exam_id)->where('attempt_no', 1)->orderBy('total_mark', 'DESC')->orderBy('attend_ended_at', 'ASC')->get();
            } else {
                // ->where('answer_uploaded','<=',$exm_details->ended_at)
                $exam_attended = ModelExamAttempt::with('user')->where('exam_id', $request->exam_id)->orderBy('total_mark', 'DESC')->orderBy('answer_uploaded', 'ASC')->get();
            }

            $leaderboardResources = $exam_attended->map(function ($item, $index) {
                return new ModelExamLeaderboardResources($item, $index + 1);
            });

            return response()->json(['data' => $leaderboardResources, 'status' => 200]);
        }
    }


    public function DailyCADates(Request $request)
    {

        $monthMapping = "CASE 
                            WHEN month = 'January' THEN 1
                            WHEN month = 'February' THEN 2
                            WHEN month = 'March' THEN 3
                            WHEN month = 'April' THEN 4
                            WHEN month = 'May' THEN 5
                            WHEN month = 'June' THEN 6
                            WHEN month = 'July' THEN 7
                            WHEN month = 'August' THEN 8
                            WHEN month = 'September' THEN 9
                            WHEN month = 'October' THEN 10
                            WHEN month = 'November' THEN 11
                            WHEN month = 'December' THEN 12
                            ELSE 13
                        END";

        $currentAffairs = CurrentAffairs::select('year', 'day', 'month')
            ->orderBy('year', 'DESC')
            ->orderBy(DB::raw($monthMapping))
            ->orderBy('day', 'ASC')
            ->get();

        $groupedData = $currentAffairs->groupBy(['year', 'month', 'day']);

        $response = [];
        foreach ($groupedData as $year => $months) {
            $yearData = ['year' => $year, 'months' => []];
            foreach ($months as $month => $days) {
                $monthData = ['month' => $month, 'days' => []];
                foreach ($days as $day => $records) {
                    //'records' => $records
                    $dayData = ['day' => $day, 'month' => $month, 'year' => $year];
                    $monthData['days'][] = $dayData;
                }
                $yearData['months'][] = $monthData;
            }
            $response[] = $yearData;
        }


        return response()->json(['data' => $response, 'status' => 200]);
    }

    public function DailyCA(Request $request)
    {
        $daily_ca_day_single = CurrentAffairs::where('year', $request->year)->where('month', $request->month)->where('day', $request->day)->get();
        return response()->json(['data' => $daily_ca_day_single, 'status' => 200]);
    }

    public function Stories(Request $request)
    {
        // each story is shown for the exam types ticked on it; apps that do not say which exam default to KPSC
        $stories         = Banner::forExam($request->input('exam', 'kpsc'))->where('status', 1)->orderBy('created_at', 'DESC')->get();

        return response()->json(['data' => BannerResources::collection($stories), 'status' => 200]);
    }
    public function Banners(Request $request)
    {
        $banner         = SpecialTab::where('status', 1)->orderby('position')->get();

        return response()->json(['data' => BannerResources::collection($banner), 'status' => 200]);
    }
    public function Notifications(Request $request)
    {
        $noftify        = Notification::orderby('created_at', 'desc')->get();
        return response()->json(['data' => $noftify, 'status' => 200]);
    }

    public function CategoriesList(Request $request)
    {
        $subjects = KpscSubject::with('child')
            ->where('status', 'show')
            ->where('type', 'parent')
            ->orderBy('position')
            ->get();

        return response()->json([
            'data' => SubjectCategoryResources::collection($subjects),
            'status' => 200,
        ]);
    }

    public function PreviousQuestionPappers(Request $request)
    {
        $prev_paper = PrevQuestion::all();
        $groupedData = $prev_paper->groupBy(['category', 'subcategory']);
        $response = [];
        foreach ($groupedData as $category => $subcategories) {
            $categoryData = ['category' => $category, 'subcategories' => []];
            foreach ($subcategories as $subcategory => $records) {
                $subcategoryData = [
                    'subcategory' => $subcategory,
                    'records' => PrevQyestionPapperResources::collection($records)
                ];
                $categoryData['subcategories'][] = $subcategoryData;
            }
            $response[] = $categoryData;
        }

        return response()->json(['data' => $response, 'status' => 200]);
    }


    public function ResultsRequesting(Request $request)
    {

        $results = Pscresults::orderBy('created_at', 'desc')->get();
        $groupedData = $results->groupBy('type');
        $response = [];
        foreach ($groupedData as $type => $records) {
            $typeData = [
                'type' => $type,
                'records' => SyllabusResources::collection($records)
            ];
            $response[] = $typeData;
        }
        return response()->json(['data' => $response, 'status' => 200]);
    }

    public function SyllabusRequesting(Request $request)
    {
        $syllabus = Syllabus::where('type', '!=', 'EXAM CALENDAR')->orderBy('created_at', 'desc')->get();
        $groupedData = $syllabus->groupBy('type');
        $response = [];
        foreach ($groupedData as $type => $records) {
            $typeData = [
                'type' => $type,
                'records' => SyllabusResources::collection($records)
            ];
            $response[] = $typeData;
        }

        return response()->json(['data' => $response, 'status' => 200]);
    }

    public function ExamCalender(Request $request)
    {

        $time_table = Syllabus::where('type', 'EXAM CALENDAR')->orderBy('created_at', 'desc')->get();
        $groupedData = $time_table->groupBy('category_no');
        $response = [];
        foreach ($groupedData as $type => $records) {
            $typeData = [
                'type' => $type,
                'records' => SyllabusResources::collection($records)
            ];
            $response[] = $typeData;
        }

        return response()->json(['data' => $response, 'status' => 200]);
    }

    public function News(Request $request) {}

    public function GameLevels(Request $request) {}

    public function refferalFriends(Request $request)
    {
        $users = User::get();
        return response()->json(['data' => UserResources::collection($users), 'status' => 200]);
    }

    public function CaDailyExams(Request $request)
    {
        $user_id = $request->user_id;

        $query = DailyExam::whereHas('exam_details')
            ->with(['ca_exam_attened' => function ($query) use ($user_id) {
                $query->where('user_id', $user_id);
            }])
            ->where('section', 'CA-Daily-Exam')
            ->where('status', 1);

        if ($request->filled('season_id')) {
            // one season, every kind of exam in it (daily, weekly, weekend, ...)
            $query->where('season_id', $request->season_id)
                ->whereHas('season', fn ($s) => $s->whereIn('status', ['active', 'completed']))
                ->orderBy('exam_date')->orderBy('slot_no');
        } else {
            // apps that do not know about seasons: the default season's daily exams, as before
            $default = \App\Models\CaSeason::where('is_featured', 1)->whereIn('status', ['active', 'completed'])->value('id');
            $query->where(function ($w) use ($default) {
                $w->whereNull('season_id')
                    ->orWhere(fn ($x) => $x->where('season_id', $default)->where('exam_kind', 'daily'));
            })->orderBy('level', 'ASC');
        }

        return response()->json(['data' => CaDailyExamListResources::collection($query->get()), 'status' => 200]);
    }

    /** POST api/ca-seasons : seasons the app can show (active + completed), default season first. */
    public function CaSeasons(Request $request)
    {
        $seasons = \App\Models\CaSeason::whereIn('status', ['active', 'completed'])
            ->orderByDesc('is_featured')->orderBy('position')->orderByDesc('id')->get();

        $counts = DailyExam::where('section', 'CA-Daily-Exam')->where('status', 1)->whereIn('season_id', $seasons->pluck('id'))
            ->whereHas('exam_details')
            ->selectRaw('season_id, exam_kind, COUNT(*) as c')->groupBy('season_id', 'exam_kind')->get()->groupBy('season_id');

        return response()->json([
            'data' => $seasons->map(fn ($s) => [
                'id' => $s->id,
                'name' => $s->name,
                'slug' => $s->slug,
                'description' => $s->description,
                'cover_image' => $s->cover_image ? url('storage/' . $s->cover_image) : null,
                'status' => $s->status,
                'schedule_type' => $s->schedule_type,
                'starts_on' => optional($s->starts_on)->toDateString(),
                'ends_on' => optional($s->ends_on)->toDateString(),
                'tags' => $s->tags ?? [],
                'is_current' => (bool) $s->is_featured,
                'ongoing' => $s->isOngoing(),
                'exam_counts' => ($counts[$s->id] ?? collect())->pluck('c', 'exam_kind'),
            ])->values(),
            'status' => 200,
        ]);
    }



    public function CaDailyExamSingle(Request $request)
    {
        if ($locked = $this->seasonLock($request->id)) {
            return $locked;
        }
        $date_details = DailyExamdetails::where('exam_id', $request->id)->inRandomOrder()->get();
        $exam = DailyExam::where('id', $request->id)->first();

        return response()->json(['data' => DailyExamDetailsResources::collection($date_details), 'exam_ended' => $exam->ended_at, 'status' => 200]);
    }

    public function CADailyExamLeaderboard(Request $request)
    {
        $exam_attended = CaDailyExamAttempt::with('user')->where('exam_id', $request->exam_id)->orderBy('total', 'desc')->orderBy('created_at', 'asc')->get();
        // $currentRank   = CaDailyExamAttempt::where('user_id', $request->user_id)->where('exam_id', $request->exam_id)->orderBy('total', 'desc')->orderBy('created_at', 'asc')->first();
        $currentRank  = null;
        foreach($exam_attended ?? [] as $key => $user){
            if($user->user_id == $request->user_id){
                $currentRank = $key+1;
            }
        }

        return response()->json(['data' => CADailyExamLeaderboardResources::collection($exam_attended), 'currentRank' => $currentRank, 'status' => 200]);
    }


    public function RankCard(Request $request){
        $data = [
            [
                'title' => '🏆 Overall Champion',
                "name" => "user user user user user user user user user user user user user user user user user user user user user",
                "image" => "https://www.pachavellam.com/assets/images/user.png",
                "star" => '3',
                "Count Star" => '40',
            ],
            [
                'title' => '🌟 Monthly Star',
                "name" => "user user ",
                "image" => "https://www.pachavellam.com/assets/images/user.png",
                "star" => '3',
                "Count Star" => '30',
            ],
            [
                'title' => '✨ Weekly Star',
                "name" => "user user user user user user user user user user user user user user user user user user user user user",
                "image" => "imageurl",
                "star" => '3',
                "Count Star" => '20',
            ],
        ];
        return response()->json(['data' => $data, 'status' => 200]);

    }
}
