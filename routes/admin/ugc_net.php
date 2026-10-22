<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'as' => 'admin', 'middleware' => ['admin.area']], function () {
    Route::group(['prefix' => 'ugc-net', 'as' => 'ugc_net.'], function () {
        Route::get('/', 'ugc_net\HomeController@dashboard');

        Route::get('interested-students', 'student\ExamStudentController@index')->defaults('exam', 'ugc-net');
        Route::get('interested-students/export', 'student\ExamStudentController@export')->defaults('exam', 'ugc-net');


        // Banners, What's New, Announcements, Notifications and Stories are common to every exam type (admin/content/..., admin/stories):
        // the old per-exam addresses open the common screen filtered to this exam type.
        foreach (config('exam_sections_common') as $section => $common) {
            Route::get($section, fn () => redirect(url($common . '?exam=ugc-net')));
            Route::get($section . '/create', fn () => redirect(url($common . '/create?exam=ugc-net')));
            Route::get($section . '/{id}/edit', fn () => redirect(url($common . '?exam=ugc-net')));
        }

        // the other content sections stay per exam type (from config/exam_sections.php)
        foreach (array_diff(array_keys(config('exam_sections')), array_keys(config('exam_sections_common'))) as $section) {
            Route::get($section, 'ugc_net\SectionController@index')->defaults('section', $section);
            Route::get($section . '/create', 'ugc_net\SectionController@create')->defaults('section', $section);
            Route::post($section, 'ugc_net\SectionController@store')->defaults('section', $section);
            Route::get($section . '/{id}/edit', 'ugc_net\SectionController@edit')->defaults('section', $section);
            Route::put($section . '/{id}', 'ugc_net\SectionController@update')->defaults('section', $section);
            Route::delete($section . '/{id}', 'ugc_net\SectionController@destroy')->defaults('section', $section);
        }
    });
});
