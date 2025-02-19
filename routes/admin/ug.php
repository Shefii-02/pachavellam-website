<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'as' => 'admin', 'middleware' => ['auth']], function () {
    Route::group(['prefix' => 'ug-pg', 'as' => 'ug-pg.'], function () {
        Route::get('/', 'ug_pg\HomeController@dashboard');
        Route::get('university', 'ug_pg\HomeController@university');
        Route::post('university', 'ug_pg\HomeController@university_store');
        Route::get('university/delete/{id}', 'ug_pg\HomeController@delete');
        Route::post('university/updating', 'ug_pg\HomeController@updating');


        Route::get('level', 'ug_pg\HomeController@level');
        Route::post('level', 'ug_pg\HomeController@level_store');
        Route::get('level/delete/{id}', 'ug_pg\HomeController@delete_level');
        Route::post('level/updating', 'ug_pg\HomeController@updating_level');

        Route::get('course', 'ug_pg\HomeController@course');
        Route::post('course', 'ug_pg\HomeController@course_store');
        Route::get('course/delete/{id}', 'ug_pg\HomeController@delete_course');
        Route::post('course/updating', 'ug_pg\HomeController@updating_course');



        Route::get('year_admission', 'ug_pg\HomeController@year_admission');
        Route::post('year_admission', 'ug_pg\HomeController@year_admission_store');
        Route::get('year_admission/delete/{id}', 'ug_pg\HomeController@delete_year_admission');
        Route::post('year_admission/updating', 'ug_pg\HomeController@updating_year_admission');

        Route::get('semester', 'ug_pg\HomeController@semester');
        Route::post('semester', 'ug_pg\HomeController@semester_store');
        Route::get('semester/delete/{id}', 'ug_pg\HomeController@delete_semester');
        Route::post('semester/updating', 'ug_pg\HomeController@updating_semester');

        Route::get('subjects', 'ug_pg\HomeController@subjects');
        Route::post('subjects', 'ug_pg\HomeController@subjects_store');
        Route::get('subjects/delete/{id}', 'ug_pg\HomeController@delete_subjects');
        Route::post('subjects/updating', 'ug_pg\HomeController@updating_subjects');



        Route::get('question-paper', 'ug_pg\HomeController@question_papper');
        Route::post('question-paper', 'ug_pg\HomeController@store_question_papper');
        Route::get('question-paper-view', 'ug_pg\HomeController@question_papper_view');
        Route::get('question-paper-single/{id}', 'ug_pg\HomeController@question_papper_single');
        Route::get('question-paper-edit/{id}', 'ug_pg\HomeController@question_papper_edit');
        Route::post('question-paper/updating', 'ug_pg\HomeController@updating_question_papper');
        Route::get('question-paper/delete/{id}', 'ug_pg\HomeController@delete_question_papper');

        Route::get('materials', 'ug_pg\HomeController@material');
        Route::post('material', 'ug_pg\HomeController@material_store');

        Route::get('materials-view', 'ug_pg\HomeController@material_view');
        Route::get('materials-single/{id}', 'ug_pg\HomeController@material_single');
        Route::get('materials-edit/{id}', 'ug_pg\HomeController@material_edit');
        Route::post('materials/updating', 'ug_pg\HomeController@updating_materials');
        Route::get('materials/delete/{id}', 'ug_pg\HomeController@delete_materials');

        Route::get('get_level', 'ug_pg\HomeController@get_level');
        Route::get('get_course', 'ug_pg\HomeController@get_course');
        Route::get('get_admission_year', 'ug_pg\HomeController@get_admission_year');

        Route::get('get_semesters', 'ug_pg\HomeController@get_semesters');
        Route::get('get_subjects', 'ug_pg\HomeController@get_subjects');

        Route::get('syllabus', 'ug_pg\HomeController@syllabus');
        Route::post('syllabus', 'ug_pg\HomeController@syllabus_store');

        Route::get('syllabus-view', 'ug_pg\HomeController@syllabus_view');
        Route::get('syllabus-single/{id}', 'ug_pg\HomeController@syllabus_single');
        Route::get('syllabus-edit/{id}', 'ug_pg\HomeController@syllabus_edit');
        Route::post('syllabus/updating', 'ug_pg\HomeController@syllabus_update');
        Route::get('syllabus/delete/{id}', 'ug_pg\HomeController@delete_syllabus');


        Route::get('banner-slider/delete/{id}', 'ug_pg\BannerController@delete');
        Route::post('banner-slider/update-slider', 'ug_pg\BannerController@update_slider');

        Route::resource('banner-slider', 'ug_pg\BannerController');

        Route::get('notifications/delete/{id}', 'ug_pg\NotificationController@delete');
        Route::post('notifications/update-notify', 'ug_pg\NotificationController@update_notify');

        Route::resource('notifications', 'ug_pg\NotificationController');

        Route::get('special-videos/delete/{id}', 'ug_pg\SpecialVideoController@delete');
        Route::post('special-videos/update-spclvideos', 'ug_pg\SpecialVideoController@update_spclvideos');

        Route::resource('special-videos', 'ug_pg\SpecialVideoController');

        Route::get('news/delete/{id}', 'ug_pg\NewsController@delete');
        Route::post('news/update-news', 'ug_pg\NewsController@update_news');

        Route::resource('news', 'ug_pg\NewsController');


        Route::get('class-request', 'ug_pg\RequestingClass@index');

        Route::get('class-request/delete/{id}', 'ug_pg\RequestingClass@delete');

        Route::get('class-request/edit/{id}', 'ug_pg\RequestingClass@edit');
        Route::post('class-request/update/{id}', 'ug_pg\RequestingClass@update');
    });
});