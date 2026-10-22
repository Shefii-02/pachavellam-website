<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'as' => 'admin', 'middleware' => ['admin.area']], function () {
    Route::group(['prefix' => 'rrb', 'as' => 'rrb.'], function () {
        Route::get('/', 'rrb\HomeController@dashboard');

        Route::get('interested-students', 'student\ExamStudentController@index')->defaults('exam', 'rrb');
        Route::get('interested-students/export', 'student\ExamStudentController@export')->defaults('exam', 'rrb');


        // Banners, What's New, Announcements, Notifications and Stories are common to every exam type (admin/content/..., admin/stories):
        // the old per-exam addresses open the common screen filtered to this exam type.
        foreach (config('exam_sections_common') as $section => $common) {
            Route::get($section, fn () => redirect(url($common . '?exam=rrb')));
            Route::get($section . '/create', fn () => redirect(url($common . '/create?exam=rrb')));
            Route::get($section . '/{id}/edit', fn () => redirect(url($common . '?exam=rrb')));
        }

        // the other content sections stay per exam type (from config/exam_sections.php)
        foreach (array_diff(array_keys(config('exam_sections')), array_keys(config('exam_sections_common'))) as $section) {
            Route::get($section, 'rrb\SectionController@index')->defaults('section', $section);
            Route::get($section . '/create', 'rrb\SectionController@create')->defaults('section', $section);
            Route::post($section, 'rrb\SectionController@store')->defaults('section', $section);
            Route::get($section . '/{id}/edit', 'rrb\SectionController@edit')->defaults('section', $section);
            Route::put($section . '/{id}', 'rrb\SectionController@update')->defaults('section', $section);
            Route::delete($section . '/{id}', 'rrb\SectionController@destroy')->defaults('section', $section);
        }
    });
});
