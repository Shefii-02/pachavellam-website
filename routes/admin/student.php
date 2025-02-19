<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'as' => 'admin', 'middleware' => ['auth']], function () {
    Route::group(['prefix' => 'students', 'as' => 'students'], function () {
        Route::get('/', 'student\HomeController@dashboard');
        Route::get('student-list', 'student\HomeController@students_list');
        Route::get('student-list/{slug}/delete', 'student\HomeController@student_delete');
    });
});
