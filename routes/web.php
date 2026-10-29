<?php

use Illuminate\Support\Facades\Route;
use  App\Http\Controllers;
use Illuminate\Http\Request;

/* Route::get("/world-cup-prediction", function(){

        $title = "World Cup 2022 Winner Prediction" ;
        $redirection = redirect()->back();
  
        return view('wc-predition',compact('title','redirection')); 
    });
*/

Route::get("app", function () {
    return view("app.index");
});

// Route::get("tab", function () {
//     return view("test.tab");
// });


require __DIR__ . '/admin/auth.php';
require __DIR__ . '/admin/general.php';
require __DIR__ . '/admin/exam.php';
require __DIR__ . '/admin/profile.php';
require __DIR__ . '/admin/kpsc.php';
require __DIR__ . '/admin/student.php';
require __DIR__ . '/admin/teaching.php';
require __DIR__ . '/admin/ug.php';
require __DIR__ . '/admin/rrb.php';
require __DIR__ . '/admin/ssc.php';
require __DIR__ . '/admin/ugc_net.php';
require __DIR__ . '/admin/ktet.php';


Route::get('captcha', 'CaptchaController@show')->middleware('throttle:60,1');
Route::get('captcha/status', 'CaptchaController@status')->middleware('throttle:60,1');
Route::post('/send-otp', 'MobileNumberController@sendOTP')->middleware(['throttle:20,1', 'captcha:otp']);
Route::post('/verify-otp', 'MobileNumberController@verifyOTP')->middleware('throttle:20,1');

Route::post('mobile-number-verification', 'MobileNumberController@mobileNoVerification');

Route::get('account-settings', 'UserController@account_settings')->name('account-settings');
Route::post('account-settings', 'UserController@account_settings_submit')->name('account-settings-submit');


// Route::get("/", function () {
//     return view("test.tab");
// });

// Route::get('/', 'HomeController@index');
Route::get('/', 'cms\HomeController@homepage');

// Teamzo chat widget loader. Their script hardcodes http:// for its API calls, which browsers block on an https site,
// so it is fetched here, pointed at https, and cached for an hour.
Route::get('chat-widget.js', function () {
    $js = \Illuminate\Support\Facades\Cache::remember('teamzo_widget_js', 3600, function () {
        $r = \Illuminate\Support\Facades\Http::timeout(8)->get('https://api.teamzo.io/api/v1/public/widget/wgt_SDRFEwKm6bT9kzqVugBNLEabCvlY.js');

        return $r->successful() ? str_replace(['http:\/\/api.teamzo.io', 'http://api.teamzo.io'], ['https:\/\/api.teamzo.io', 'https://api.teamzo.io'], $r->body()) : null;
    });

    return response($js ?: '/* chat unavailable */', 200, ['Content-Type' => 'application/javascript; charset=utf-8', 'Cache-Control' => 'public, max-age=3600']);
});

// Route::get("new-home", function(){
//   return view("test.new-home");
// });
Route::get('new-home', 'HomeController@index');

Route::get("/chat-sample", function () {
    return view("test.index");
});

Route::get("sign-in", function () {
    if (\auth()->check()) {
        return redirect('/');
    }
    return view("kpsc.sample-login-page");
})->name('sign-in');

Route::get("sign-in", function () {
    if (\auth()->check()) {
        return redirect('/');
    }
    return view("kpsc.sample-login-page");
})->name('login');


Route::get("sign-out", function () {
    if ($back = \App\Http\Controllers\Staff\ImpersonationController::returnToAdmin(request())) {
        return $back;
    }
    \Auth::logout();
    return redirect()->back();
})->name('sign-out');


Route::get('feedback/{author}', 'HomeController@feedback_form');

Route::post('feedback/{author}', 'HomeController@feedback_submit');

Route::get('info/{title}', 'HomeController@info');

Route::get('/daily_ca', 'HomeController@daily_ca_get');
Route::post('/daily_ca', 'HomeController@daily_ca_store');

Route::get('/upload-pscbullet-in', 'HomeController@upload_pscbullet_in');
Route::post('/upload-pscbullet-in', 'HomeController@upload_pscbullet_in_store');

Route::post('ckeditor/upload', 'CkeditorController@upload')->name('ckeditor.upload');

// Route::controller(LoginController::class)->group(function(){

//     Route::get('sign-in', 'index')->name('sign-in.index');

//     Route::post('sign-in', 'check')->name('sign-in.check');


//     Route::get('sign-up', 'create')->name('sign-up.create');

//     Route::post('sign-up', 'store')->name('sign-up.store');

//     Route::get('sign-out', 'sign_out')->name('sign-out.sign_out');

// });

Route::controller(UserController::class)->group(function () {
    Route::get('register', 'register')->name('register.register');
    Route::post('stage1', 'stage1_store')->name('stage1.create');
    Route::post('stage2', 'stage2_store')->name('stage2.store');
});


// Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
//     return view('dashboard');
// })->name('dashboard');

Route::controller(GoogleController::class)->group(function () {
    Route::get('auth/google', 'redirectToGoogle')->name('auth.google');
    Route::get('auth/google/callback', 'handleGoogleCallback');
});

// Route::controller(FacebookController::class)->group(function () {
//     Route::get('auth/facebook', 'redirectToFacebook')->name('auth.facebook');
//     Route::get('auth/facebook/callback', 'handleFacebookCallback');
// });



// ,'middleware' => ['auth']



Route::post('sign-in', 'cms\HomeController@daily_exam_user_login')->name('sign-in-check');
Route::post('sign-up', 'cms\HomeController@daily_exam_user_register')->name('sign-up-store');


Route::get('/kpsc', fn () => app(\App\Http\Controllers\PublicCatalogController::class)->exam('kpsc'));   // the KPSC exam type page (was the home page)
Route::get('/kpsc/home', fn () => redirect(url('kpsc'), 301));   // KPSC home = the KPSC exam type page, like every other exam type

require __DIR__ . '/kpsc.php';
require __DIR__ . '/ug.php';


Route::get('about-us', function () {
    return view('cms.about-us');
});

Route::get('contact-us', function () {
    return view('cms.contact-us');
});

Route::get('faq', function () {
    return view('cms.faq');
});


Route::get('firebase-messaging-sw.js', function () {
    $cfg = json_encode(config('firebase.web.config'));
    $js = "importScripts('https://www.gstatic.com/firebasejs/10.12.2/firebase-app-compat.js','https://www.gstatic.com/firebasejs/10.12.2/firebase-messaging-compat.js');\n"
        . "firebase.initializeApp($cfg);\nconst messaging = firebase.messaging();\n"
        . "self.addEventListener('notificationclick', function (e) { e.notification.close(); var u = (e.notification.data && (e.notification.data.FCM_MSG && e.notification.data.FCM_MSG.data ? e.notification.data.FCM_MSG.data.landing_url : e.notification.data.landing_url)) || '/'; e.waitUntil(clients.openWindow(u)); });\n";
    return response($js, 200, ['Content-Type' => 'application/javascript', 'Service-Worker-Allowed' => '/']);
});
Route::post('push/web-token', function (\Illuminate\Http\Request $r) {
    abort_unless(auth()->check(), 401);
    $r->validate(['token' => 'required|string|max:255']);
    \App\Models\DeviceToken::updateOrCreate(['token' => $r->token], ['user_id' => auth()->id(), 'platform' => 'web', 'device_name' => substr((string) $r->userAgent(), 0, 120), 'last_seen_at' => now()]);
    return response()->json(['ok' => true]);
})->middleware('throttle:30,1');
Route::get('announcements', 'PublicCatalogController@announcements');
Route::get('announcements/{id}', 'PublicCatalogController@announcement')->whereNumber('id');
Route::get('banners', 'PublicCatalogController@banners');
Route::get('achievements', fn () => redirect('stories'));
Route::get('stories', 'PublicCatalogController@stories');
Route::get('stories-feed', 'PublicCatalogController@storiesFeed');
Route::get('notifications-feed', 'PublicCatalogController@feed');
// bottom bar tabs: Exams, Courses, Learn (open to everyone) and Profile (signed in); choosing an exam / favourite asks to sign in, then finishes the choice
Route::get('exams', 'StudentHubController@exams');
Route::get('courses', 'StudentHubController@courses');
Route::get('learn', 'StudentHubController@learn');
Route::middleware(['auth', 'throttle:60,1'])->group(function () {
    Route::match(['get', 'post'], 'exams/interest/{id}', 'StudentHubController@interest')->whereNumber('id');
    Route::match(['get', 'post'], 'exams/favourite/{id}', 'StudentHubController@favourite')->whereNumber('id');
    Route::get('my-account', 'StudentHubController@account');
    Route::get('my-account/trend', 'StudentHubController@trend');
    Route::get('my-courses/{id}', 'MyCourseController@show')->whereNumber('id');   // a bought course, tab by tab
    Route::post('my-account/referral', 'ReferralController@apply')->middleware('throttle:10,1');
    Route::post('my-account/profile','StudentHubController@updateProfile');
    Route::post('my-account/password', 'StudentHubController@updatePassword');
});
// signed in devices: the sign in stopped because of the device limit; my devices
Route::get('devices/limit', 'DeviceController@limit');
Route::post('devices/limit/release', 'DeviceController@release')->middleware('throttle:20,1');
Route::middleware('auth')->group(function () {
    Route::get('devices', 'DeviceController@index');
    Route::post('devices/{id}/remove', 'DeviceController@remove')->whereNumber('id');
    Route::post('devices/logout-others', 'DeviceController@logoutOthers');
});
Route::get('r/{code}', 'ReferralController@landing')->where('code', '[A-Za-z0-9]{4,12}');   // refer & earn link, shared from My account
Route::get('free-classes', 'PublicCatalogController@freeClasses');
Route::get('free-classes/{id}', 'PublicCatalogController@freeClassById')->whereNumber('id');   // old numeric links go to the random-id address
Route::get('free-classes/{uid}', 'PublicCatalogController@freeClass')->where('uid', '(?=[0-9]*[a-z])[a-z0-9]{8,10}');
Route::get('player/{uid}/chat', 'ClassChatController@messages')->where('uid', '[a-z0-9]{8,14}')->middleware('throttle:90,1');
Route::post('player/{uid}/chat', 'ClassChatController@send')->where('uid', '[a-z0-9]{8,14}')->middleware(['auth', 'throttle:20,1']);
Route::get('watch/{vid}', 'PublicCatalogController@watch')->where('vid', '[a-z0-9]{8,14}');
Route::get('player/{uid}', 'PublicCatalogController@player')->where('uid', '[a-z0-9]{8,14}');
Route::get('p/{slug}', 'Content\SitePageController@show')->where('slug', '[a-z0-9-]+');   // custom pages written in the panel
Route::get('c/{path}', 'PublicCatalogController@category')->where('path', '[a-z0-9\-/]+');   // exam category / sub category by slug: its articles, free videos, courses
Route::get('articles', 'PublicCatalogController@articles');
Route::get('articles/{id}', 'PublicCatalogController@article')->whereNumber('id');
Route::get('courses/{slug}', 'PublicCatalogController@course');
// exam type pages live at /kpsc, /rrb ... (the old /exam/kpsc address redirects there)
Route::get('exam/{slug}', fn ($slug) => redirect(url($slug), 301));

// Prime: landing page of the paid courses; buying needs a signed in student (the sign in page brings them back here)
Route::get('prime', 'PrimeController@index');
Route::get('prime/buy/{id}', 'PrimeController@buy')->whereNumber('id')->middleware(['auth', 'throttle:20,1']);
Route::post('counselling', 'Content\CounsellingController@store')->middleware(['throttle:5,1', 'captcha:counselling']);

// Payment links: a student pays for one course on a web page (sent on WhatsApp, or opened from the website / apps)
Route::get('pay/{token}', 'Finance\PayLinkPageController@show')->where('token', '[A-Za-z0-9]{40}');
Route::post('pay/{token}/start', 'Finance\PayLinkPageController@start')->where('token', '[A-Za-z0-9]{40}')->middleware('throttle:20,1');
Route::post('pay/{token}/verify', 'Finance\PayLinkPageController@verify')->where('token', '[A-Za-z0-9]{40}')->middleware('throttle:30,1');
Route::get('pay/{token}/status', 'Finance\PayLinkPageController@status')->where('token', '[A-Za-z0-9]{40}')->middleware('throttle:60,1');

// builder exams on the website: instructions (open to all), then the test, result, solutions and the student's own list / analysis
Route::get('tests/{id}', 'TestController@show')->whereNumber('id');
Route::middleware('auth')->group(function () {
    Route::get('tests/{id}/sign-in', fn ($id) => redirect(url('tests/' . (int) $id)))->whereNumber('id');   // guest: sign in, then back to the test
    Route::post('tests/{id}/start', 'TestController@start')->whereNumber('id')->middleware('throttle:20,1');
    Route::get('tests/attempt/{id}', 'TestController@play')->whereNumber('id');
    Route::post('tests/attempt/{id}/save', 'TestController@save')->whereNumber('id')->middleware('throttle:120,1');
    Route::get('tests/attempt/{id}/result', 'TestController@result')->whereNumber('id');
    Route::get('tests/attempt/{id}/solutions', 'TestController@solutions')->whereNumber('id');
    Route::post('tests/attempt/{id}/rate', 'TestController@rate')->whereNumber('id')->middleware('throttle:10,1');
    Route::post('tests/{id}/report', 'TestController@report')->whereNumber('id')->middleware('throttle:20,1');
    Route::get('my-tests', 'TestController@mine');
    Route::get('my-tests/analysis', 'TestController@analysis');
});

// a season of an exam series: what it holds, its price, Buy
Route::get('seasons/{id}', 'SeasonController@show')->whereNumber('id');
Route::get('seasons/{id}/buy', 'SeasonController@buy')->whereNumber('id')->middleware(['auth', 'throttle:20,1']);

// exam series of an exam type: /kpsc/daily-exams, /rrb/omr-exams, /ssc/gk-exams, /kpsc/ca-exams
foreach (['daily-exams', 'omr-exams', 'gk-exams', 'ca-exams', 'special-exams'] as $seriesPage) {
    Route::get('{examSlug}/' . $seriesPage . '/{seasonSlug}', 'SeasonController@showBySlug')->where(['examSlug' => '[a-z0-9-]+', 'seasonSlug' => '[a-z0-9-]+'])->defaults('page', $seriesPage);
    Route::get('{examSlug}/' . $seriesPage, fn (\Illuminate\Http\Request $r, $examSlug) => app(\App\Http\Controllers\PublicCatalogController::class)->series($r, $examSlug, $seriesPage))->where('examSlug', '[a-z0-9-]+');
}

// /{exam type slug}: the landing page of an exam type. Every route above wins first, so only addresses nothing else uses get here.
Route::fallback(function (\Illuminate\Http\Request $request) {
    $slug = trim($request->path(), '/');
    abort_unless($request->isMethod('GET') && preg_match('/^[a-z0-9-]+$/', $slug) && \App\Models\CompetitiveExam::where('slug', $slug)->where('status', 1)->exists(), 404);

    return app(\App\Http\Controllers\PublicCatalogController::class)->exam($slug);
});
