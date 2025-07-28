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


require __DIR__ . '/admin/general.php';
require __DIR__ . '/admin/kpsc.php';
require __DIR__ . '/admin/student.php';
require __DIR__ . '/admin/teaching.php';
require __DIR__ . '/admin/ug.php';


Route::post('/send-otp', 'MobileNumberController@sendOTP');
Route::post('/verify-otp', 'MobileNumberController@verifyOTP');

Route::post('mobile-number-verification', 'MobileNumberController@mobileNoVerification');

Route::get('account-settings', 'UserController@account_settings')->name('account-settings');
Route::post('account-settings', 'UserController@account_settings_submit')->name('account-settings-submit');


// Route::get("/", function () {
//     return view("test.tab");
// });

// Route::get('/', 'HomeController@index');
Route::get('/', 'cms\HomeController@homepage');

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


Route::get('/kpsc', 'cms\HomeController@homepage');
Route::get('/kpsc/home', 'cms\HomeController@homepage');

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
