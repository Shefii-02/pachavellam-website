<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'as' => 'admin', 'middleware' => ['admin.area']], function () {
    Route::group(['prefix' => 'ssc', 'as' => 'ssc.'], function () {
        Route::get('/', 'ssc\HomeController@dashboard');

        Route::get('interested-students', 'student\ExamStudentController@index')->defaults('exam', 'ssc');
        Route::get('interested-students/export', 'student\ExamStudentController@export')->defaults('exam', 'ssc');


        // Banners, What's New, Announcements, Notifications and Stories are common to every exam type (admin/content/..., admin/stories):
        // the old per-exam addresses open the common screen filtered to this exam type.
        foreach (config('exam_sections_common') as $section => $common) {
            Route::get($section, fn () => redirect(url($common . '?exam=ssc')));
            Route::get($section . '/create', fn () => redirect(url($common . '/create?exam=ssc')));
            Route::get($section . '/{id}/edit', fn () => redirect(url($common . '?exam=ssc')));
        }

        // the other content sections stay per exam type (from config/exam_sections.php)
        foreach (array_diff(array_keys(config('exam_sections')), array_keys(config('exam_sections_common'))) as $section) {
            Route::get($section, 'ssc\SectionController@index')->defaults('section', $section);
            Route::get($section . '/create', 'ssc\SectionController@create')->defaults('section', $section);
            Route::post($section, 'ssc\SectionController@store')->defaults('section', $section);
            Route::get($section . '/{id}/edit', 'ssc\SectionController@edit')->defaults('section', $section);
            Route::put($section . '/{id}', 'ssc\SectionController@update')->defaults('section', $section);
            Route::delete($section . '/{id}', 'ssc\SectionController@destroy')->defaults('section', $section);
        }
    });
});
