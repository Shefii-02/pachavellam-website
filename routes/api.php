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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('exams', 'Api\ExamInterestController@exams');

// our own captcha: a new picture, and whether a form needs one right now
Route::get('captcha', 'CaptchaController@show')->middleware('throttle:60,1');
Route::get('captcha/status', 'CaptchaController@status')->middleware('throttle:60,1');

// the website's pages as JSON for the apps (a student's token is optional: with it, locks and purchases are known)
Route::prefix('app')->group(function () {
    Route::get('home', 'Api\AppCatalogController@home');
    Route::get('exams/{slug}', 'Api\AppCatalogController@exam')->where('slug', '[a-z0-9-]+');
    Route::get('exams/{slug}/series/{series}', 'Api\AppCatalogController@series')->where('slug', '[a-z0-9-]+')->where('series', 'daily-exams|omr-exams|gk-exams|ca-exams|special-exams');
    Route::get('seasons/{id}', 'Api\AppCatalogController@season')->whereNumber('id');
    Route::get('ranking', 'Api\AppCatalogController@ranking');
    Route::get('stories', 'Api\AppCatalogController@stories');
    Route::get('ads', 'Api\AppCatalogController@ads');
    Route::get('courses', 'Api\AppCatalogController@courses');
    Route::get('categories/{slug}', 'Api\AppCatalogController@category')->where('slug', '[a-z0-9-]+');
    Route::get('articles', 'Api\AppCatalogController@articles');
    Route::get('courses/{slug}', 'Api\AppCatalogController@courseDetail')->where('slug', '[A-Za-z0-9_-]+');
    Route::get('classes', 'Api\AppCatalogController@classes');
    Route::get('class-playlists', 'Api\AppCatalogController@playlists');
    Route::get('articles/{id}', 'Api\AppCatalogController@article')->whereNumber('id');
    Route::get('announcements/{id}', 'Api\AppCatalogController@announcement')->whereNumber('id');
    Route::get('classes/{uid}/chat', 'Api\ClassChatApiController@messages')->where('uid', '[a-z0-9]{8,14}')->middleware('throttle:90,1');
});

Route::group(['prefix' => 'auth'], function () {
    Route::post('google-sign-in', 'Api\AuthController@googleSignIn');
    Route::post('signin', 'Api\AuthController@SignIn')->middleware(['throttle:30,1', 'captcha:login']);
    Route::post('signup', 'Api\AuthController@SignUp')->middleware(['throttle:20,1', 'captcha:signup']);
    Route::post('send-otp', 'Api\AuthController@sendOtp')->middleware(['throttle:20,1', 'captcha:otp']);
    Route::post('verify-otp', 'Api\AuthController@verifyOtp')->middleware('throttle:20,1');
    Route::post('devices/release', 'Api\AuthController@releaseDevices')->middleware('throttle:20,1');   // too many devices: remove some, then sign in

    // Route::post('forget-password', 'Api\AuthController@forgetPassword');
    Route::post("forget-password", function () {
        return response()->json(['data' => 'Email Sended Successfully sended"', 'status' => 200]);
    });

    Route::middleware('auth:api')->group(function () {
        Route::post('refresh', 'Api\AuthController@refresh');
        Route::get('devices', 'Api\AuthController@devices');
        Route::delete('devices/{id}', 'Api\AuthController@removeDevice')->whereNumber('id');
        Route::post('devices/logout-others', 'Api\AuthController@logoutOthers');
        Route::post('logout', 'Api\AuthController@logout');
        Route::get('me', 'Api\AuthController@me');
        Route::post('update-profile', 'Api\AuthController@UpdateProfile');
        Route::post('delete-myaccount', 'Api\AuthController@deleteMyaccount');
        Route::get('referral', 'ReferralController@show');
        Route::post('seasons/{id}/buy', 'Api\AppCatalogController@seasonBuy')->whereNumber('id')->middleware('throttle:20,1');
        Route::get('my-courses', 'Api\AppCatalogController@myCourses');
        Route::get('account-summary', 'Api\AppCatalogController@account');
        Route::post('classes/{uid}/chat', 'Api\ClassChatApiController@send')->where('uid', '[a-z0-9]{8,14}')->middleware('throttle:20,1');
        Route::get('my-courses/{id}', 'Api\AppCatalogController@myCourse')->whereNumber('id');
        Route::post('referral/apply', 'ReferralController@applyApi')->middleware('throttle:10,1');
        Route::post('courses/{id}/join', 'Api\AppCatalogController@courseJoin')->whereNumber('id')->middleware('throttle:20,1');
        Route::get('push-preference', 'Api\DeviceTokenController@preference');
        Route::post('push-preference', 'Api\DeviceTokenController@setPreference');
        Route::post('device-tokens', 'Api\DeviceTokenController@register');
        Route::delete('device-tokens', 'Api\DeviceTokenController@destroy');

        // exams the student is interested in
        Route::get('interests', 'Api\ExamInterestController@mine');
        Route::post('interests', 'Api\ExamInterestController@sync');
    });
});


Route::post("app-sections", function () {
    $data = [
        'StoriesSection' => true,
        'BannerSection' => true,
        'RankCard' => false,
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

// course catalogue for every exam type (published courses), filterable by exam and category
Route::post('exam-course-categories', 'Api\ExamCourseController@categories');
Route::post('exam-courses', 'Api\ExamCourseController@index');
Route::post('exam-course-single', 'Api\ExamCourseController@single');
// does this coupon work for this course, and what would the student pay? (read only - nothing is used up)
Route::post('exam-coupon-check', 'Api\ExamCouponController@check')->middleware(['auth:api', 'throttle:30,1']);

// joining a course: payment (Razorpay / PhonePe), status, and the student's courses
Route::middleware(['auth:api', 'throttle:30,1'])->group(function () {
    Route::post('course-checkout', 'Api\CoursePaymentController@checkout');
    Route::post('course-payment-link', 'Api\CoursePaymentController@paymentLink');
    Route::post('course-payment-verify', 'Api\CoursePaymentController@verify');
    Route::post('course-payment-status', 'Api\CoursePaymentController@status');
    Route::post('my-course-admissions', 'Api\CoursePaymentController@mine');
});
// called by the gateways themselves (no login; every call is checked by signature)
Route::post('payments/razorpay/webhook', 'Api\CoursePaymentController@razorpayWebhook');
Route::post('payments/phonepe/callback', 'Api\CoursePaymentController@phonepeCallback');
Route::match(['get', 'post'], 'payments/phonepe/return/{ref}', 'Api\CoursePaymentController@phonepeReturn');
Route::post('ca-seasons', 'Api\ApiCollectionController@CaSeasons');
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



Route::post('rank-card', 'Api\ApiCollectionController@RankCard');

