<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'ug-pg', 'as' => 'ug-pg'], function () {
    Route::get('/', 'ug_pg\FrontviewController@index');
    Route::get('news-all', 'ug_pg\FrontviewController@news_all');
    Route::get('news/{id}', 'ug_pg\FrontviewController@news_view');
    Route::get('notify', 'ug_pg\FrontviewController@notify');
    Route::get('request-class', 'ug_pg\FrontviewController@request_class');
    Route::post('request-class', 'ug_pg\FrontviewController@request_class_store');
    Route::get('register-success', 'ug_pg\FrontviewController@register_success');
    Route::get('syllabus', 'ug_pg\FrontviewController@syllabus');
    Route::get('syllabus/{university}', 'ug_pg\FrontviewController@univrsity_syllabus');
    Route::get('syllabus/{university}/{level}', 'ug_pg\FrontviewController@univrsity_level_syllabus');
    Route::get('syllabus/{university}/{level}/{title}', 'ug_pg\FrontviewController@syllabus_single_view');
    Route::get('syllabus/{university}/{level}/{title}/download', 'ug_pg\FrontviewController@syllabus_single_download');

    Route::get('materials', 'ug_pg\FrontviewController@material');
    Route::get('materials/{university_name}', 'ug_pg\FrontviewController@material_university');
    Route::get('materials/{university_name}/{level}', 'ug_pg\FrontviewController@material_level');
    Route::get('materials/{university}/{level}/{course}', 'ug_pg\FrontviewController@material_course');
    Route::get('materials/{university}/{level}/{course}/{semester}', 'ug_pg\FrontviewController@material_semester');
    Route::get('materials/{university}/{level}/{course}/{semester}/{subject}', 'ug_pg\FrontviewController@material_subject');
    // Route::get('materials/{university}/{level}/{course}/{semester}/{subject}/{single_view}', 'ug_pg\FrontviewController@material_singleview');
    Route::get('materials/{university}/{level}/{course}/{semester}/{subject}/{single_view}', 'ug_pg\FrontviewController@material_single_view');




    Route::get('university', 'ug_pg\FrontviewController@university');
    Route::get('category', 'ug_pg\FrontviewController@category');
    Route::get('/{section}', 'ug_pg\FrontviewController@university');
    Route::get('{section}/category/{university_name}', 'ug_pg\FrontviewController@category');
    Route::get('course', 'ug_pg\FrontviewController@course');
    Route::get('{section}/course/{university_name}/{category_name}', 'ug_pg\FrontviewController@course');
    Route::get('admission_year', 'ug_pg\FrontviewController@admission_year');
    Route::get('{section}/admission_year/{university_name}/{category_name}/{course_name}', 'ug_pg\FrontviewController@admission_year');
    Route::get('semester', 'ug_pg\FrontviewController@semester');
    Route::get('{section}/semester/{university_name}/{category_name}/{course_name}', 'ug_pg\FrontviewController@semester');
    Route::get('subjects', 'ug_pg\FrontviewController@subjects');
    Route::get('{section}/subjects/{university_name}/{category_name}/{course_name}/{semester_name}', 'ug_pg\FrontviewController@subjects');

    Route::get('{section}/{university_name}/{category_name}/{course_name}/{semester_name}/{subject_name}', 'ug_pg\FrontviewController@question_papers');
    Route::get('{section}/{university_name}/{category_name}/{course_name}/{semester_name}/{subject_name}/{title}', 'ug_pg\FrontviewController@question_papers_single');
});
