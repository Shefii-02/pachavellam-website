<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});





Route::group(['prefix' => 'auth'], function () {
    Route::post('google-sign-in', 'Api\AuthController@googleSignIn');
    Route::post('signin', 'Api\AuthController@SignIn');
    Route::post('signup', 'Api\AuthController@SignUp');

    Route::post('update-profile', 'Api\AuthController@UpdateProfile');

    Route::post('delete-myaccount', 'Api\AuthController@deleteMyaccount');

    // Route::post('forget-password', 'Api\AuthController@forgetPassword');


    Route::post("forget-password", function () {
        return response()->json(['data' => 'Email Sended Successfully sended"', 'status' => 200]);
    });
});


Route::post("app-sections", function () {
    $data = [
        'StoriesSection' => true,
        'BannerSection' => true,
        'SpecialSeries' => [
            'tab1' => true,
            'tab2' => false,
            'tab3' => false
        ],
        'SpecialTabScreen' => [
            'tab1' => true,
            'tab2' => true,
            'tab3' => true,
            'tab4' => true
        ],
        'CategoryScreen' => false
    ];
    return response()->json(['data' => $data, 'status' => 200]);
});


Route::post('bullet-in', 'Api\ApiCollectionController@BulletIn');
Route::post('feeds', 'Api\ApiCollectionController@FeedList');
Route::post('categories', 'Api\ApiCollectionController@CategoriesList');
Route::post('daily-exams', 'Api\ApiCollectionController@DailyExams');
Route::post('daily-exam-single', 'Api\ApiCollectionController@DailyExamSingle');
Route::post('daily-exam-leaderboard', 'Api\ApiCollectionController@DailyExamLeaderboard');


Route::post('model-exams', 'Api\ApiCollectionController@ModelExams');
Route::post('model-exam-single', 'Api\ApiCollectionController@ModelExamSingle');
Route::post('model-exam-leaderboard', 'Api\ApiCollectionController@ModelExamLeaderboard');
Route::post('daily-ca/dates', 'Api\ApiCollectionController@DailyCADates');
// Route::post('daily-ca/month', 'Api\ApiCollectionController@DailyCAMonth');
// Route::post('daily-ca/day', 'Api\ApiCollectionController@DailyCADay');
Route::post('daily-ca', 'Api\ApiCollectionController@DailyCA');
Route::post('stories', 'Api\ApiCollectionController@Stories');
Route::post('banners', 'Api\ApiCollectionController@Banners');
Route::post('notifications', 'Api\ApiCollectionController@Notifications');

Route::post('previous-question-pappers', 'Api\ApiCollectionController@PreviousQuestionPappers');
Route::post('results', 'Api\ApiCollectionController@ResultsRequesting');
Route::post('syllabus', 'Api\ApiCollectionController@SyllabusRequesting');
Route::post('exam-calender', 'Api\ApiCollectionController@ExamCalender');
Route::post('news', 'Api\ApiCollectionController@News');

Route::post('game-levels', 'Api\ApiCollectionController@GameLevels');

Route::post('refferal-friends', 'Api\ApiCollectionController@refferalFriends');

Route::post('ca-daily-exams', 'Api\ApiCollectionController@CaDailyExams');
Route::post('ca-daily-exam-single', 'Api\ApiCollectionController@CaDailyExamSingle');

Route::post('ca-exam-leaderboard', 'Api\ApiCollectionController@CADailyExamLeaderboard');


Route::post('increase-bulletin-download-count/{id}', 'Api\ApiDataSaverController@BulletInDownload');
Route::post('model-exam-uploadmark', 'Api\ApiDataSaverController@ModelExamUploadMark');

Route::post('feeds/add-comment', 'Api\ApiDataSaverController@FeedCommentStore');
Route::post('feeds/add-like', 'Api\ApiDataSaverController@FeedAddLike');
Route::post('daily-exam-single/store', 'Api\ApiDataSaverController@DailyExamSingleStore');
Route::post('ca-daily-exam-single/store', 'Api\ApiDataSaverController@CaDailyExamStore');
Route::post('feeds/report-post', 'Api\ApiDataSaverController@FeedReportRequest');
Route::post('feeds/report-comment', 'Api\ApiDataSaverController@FeedReportCommentRequest');
Route::post('chat/report-message', 'Api\ApiDataSaverController@ReportMessageRequest');
