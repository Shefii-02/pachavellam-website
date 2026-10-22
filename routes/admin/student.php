<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'as' => 'admin', 'middleware' => ['admin.area']], function () {
    Route::group(['prefix' => 'students', 'as' => 'students'], function () {
        Route::get('/', 'student\HomeController@dashboard');
        Route::get('student-list', 'student\HomeController@students_list');
        Route::get('student-list/export', 'student\HomeController@students_export');
        // competitive exams master list + student interests
        Route::get('exams', 'student\ExamController@index');
        Route::post('student-list/{id}/interests', 'student\ExamController@saveInterests')->whereNumber('id');

        // interested students of an exam that has no module of its own (future exams)
        Route::get('exam/{exam}', 'student\ExamStudentController@index');
        Route::get('exam/{exam}/export', 'student\ExamStudentController@export');

        // refer & earn
        Route::get('referrals', 'student\ReferralAdminController@index');
        Route::get('referrals/settings', 'student\ReferralAdminController@settings');
        Route::post('referrals/settings', 'student\ReferralAdminController@saveSettings');
        Route::post('referrals/{id}/reward', 'student\ReferralAdminController@reward')->whereNumber('id');
        Route::post('referrals/{id}/reject', 'student\ReferralAdminController@reject')->whereNumber('id');
        Route::post('student-list/{id}/coins', 'student\ReferralAdminController@coins')->whereNumber('id');

        Route::get('platforms', 'student\PlatformController@index');
        Route::get('platforms/export', 'student\PlatformController@export');
        Route::get('analytics', 'student\InsightController@analytics');
        Route::get('student-list/{id}', 'student\InsightController@profile')->whereNumber('id');
        Route::get('student-list/{slug}/delete', 'student\HomeController@student_delete');
    });
});
