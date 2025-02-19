<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'as' => 'admin', 'middleware' => ['auth']], function () {
    Route::group(['prefix' => 'teaching', 'as' => 'teaching.'], function () {
        Route::get('/', 'teaching\HomeController@dashboard');

        Route::get('instructors', 'teaching\InstructorController@instructors');
        Route::post('instructor', 'teaching\InstructorController@instructor_store');
        Route::get('instructor/delete/{id}', 'teaching\InstructorController@delete_instructor');
        Route::post('instructor/updating', 'teaching\InstructorController@updating_instructor');

        Route::get('course', 'teaching\CourseController@course');
        Route::post('course', 'teaching\CourseController@course_store');
        Route::get('course/delete/{id}', 'teaching\CourseController@delete_course');
        Route::post('course/updating', 'teaching\CourseController@updating_course');

        Route::get('class', 'teaching\ClassController@class');
        Route::post('class', 'teaching\ClassController@class_store');
        Route::get('class/delete/{id}', 'teaching\ClassController@delete_class');
        Route::post('class/updating', 'teaching\ClassController@updating_class');
    });
});
