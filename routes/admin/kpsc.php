<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'as' => 'admin', 'middleware' => ['auth']], function () {

    Route::group(['prefix' => 'kpsc', 'as' => 'kpsc.', 'middleware' => ['auth']], function () {


        Route::get('/', 'cms\DashboardController@dashboard');

        Route::get('banner-slider/delete/{id}', 'cms\BannerController@delete')->name('banner-slider.delete');
        Route::post('banner-slider/update-slider', 'cms\BannerController@update_slider');

        Route::resource('banner-slider', 'cms\BannerController');

        Route::get('notifications/delete/{id}', 'cms\NotificationController@delete')->name('notifications.delete');
        Route::post('notifications/update-notify', 'cms\NotificationController@update_notify');

        Route::resource('notifications', 'cms\NotificationController');

        Route::get('whats-new/delete/{id}', 'cms\WhatsNewController@delete')->name('whats-new.delete');
        Route::post('whats-new/update-whatsnew', 'cms\WhatsNewController@update_whatsnew');

        Route::resource('whats-new', 'cms\WhatsNewController');

        Route::get('special-tab/delete/{id}', 'cms\SpecialTabController@delete')->name('special-tab.delete');
        Route::post('special-tab/update-specialtab', 'cms\SpecialTabController@update_specialtab');

        Route::resource('special-tab', 'cms\SpecialTabController');

        Route::get('our-videos/delete/{id}', 'cms\OurvideosController@delete')->name('our-videos.delete');
        Route::post('our-videos/update-ourvideos', 'cms\OurvideosController@update_ourvideos');

        Route::resource('our-videos', 'cms\OurvideosController');

        Route::get('special-videos/delete/{id}', 'cms\SpecialVideoController@delete')->name('special-videos.delete');
        Route::post('special-videos/update-spclvideos', 'cms\SpecialVideoController@update_spclvideos');

        Route::resource('special-videos', 'cms\SpecialVideoController');

        Route::get('psc-news/delete/{id}', 'cms\PscnewsController@delete')->name('psc-news.delete');
        Route::post('psc-news/update-pscnews', 'cms\PscnewsController@update_pscnews');

        Route::resource('psc-news', 'cms\PscnewsController');

        Route::get('psc-bulletin/delete/{id}', 'cms\BulletinController@delete')->name('psc-bulletin.delete');
        Route::post('psc-bulletin/update-bulletin', 'cms\BulletinController@update_bulletin');

        Route::resource('psc-bulletin', 'cms\BulletinController');

        Route::get('prev-qstn/delete/{id}', 'cms\PrevQuestionController@delete')->name('prev-qstn.delete');
        Route::post('prev-qstn/update-prevqstn/{id}', 'cms\PrevQuestionController@update');

        Route::resource('prev-qstn', 'cms\PrevQuestionController');


        Route::get('syllabus/delete/{id}', 'cms\SyllabusController@delete')->name('syllabus.delete');
        Route::post('syllabus/update-syllabus', 'cms\SyllabusController@update_syllabus');

        Route::resource('syllabus', 'cms\SyllabusController');


        Route::get('new-qa/delete/{id}', 'cms\NewmodalQaController@delete')->name('new-qa.delete');
        Route::post('new-qa/update-qa', 'cms\NewmodalQaController@update');
        Route::post('new-qa/upload-csvtype', 'cms\NewmodalQaController@csvtype_store');
        Route::resource('new-qa', 'cms\NewmodalQaController');


        Route::get('material/delete/{id}', 'cms\MaterialController@delete')->name('material.delete');
        Route::post('material/update-material', 'cms\MaterialController@update_material');

        Route::resource('material', 'cms\MaterialController');


        Route::get('capsule/delete/{id}', 'cms\CapsuleController@delete')->name('capsule.delete');
        Route::post('capsule/update-capsule', 'cms\CapsuleController@update_capsule');

        Route::resource('capsule', 'cms\CapsuleController');

        Route::get('psc-results/delete/{id}', 'cms\PscresultsController@delete')->name('psc-results.delete');
        Route::post('psc-results/update-pscresults', 'cms\PscresultsController@update_pscresults');

        Route::resource('psc-results', 'cms\PscresultsController');

        Route::get('daily-ca/delete/{id}', 'cms\CurrentAffairsController@delete')->name('daily-ca.delete');
        Route::post('daily-ca/update-pscresults', 'cms\CurrentAffairsController@update_dailyca');
        Route::post('daily-ca/daily-ca-linktype', 'cms\CurrentAffairsController@linktype_store');
        Route::post('daily-ca/daily-ca-csvtype', 'cms\CurrentAffairsController@csvtype_store');

        Route::post('daily-ca/daily-ca-pdftype', 'cms\CurrentAffairsController@pdftype_store');

        Route::get('daily-ca/fetch-months', 'cms\CurrentAffairsController@fetch_months');
        Route::get('daily-ca/fetch-months-based', 'cms\CurrentAffairsController@fetch_month_basedlist');


        Route::get('daily-ca/daily-ca-show', 'cms\CurrentAffairsController@ca_showlist');

        Route::post('daily-ca/edit-ca-day-update', 'cms\CurrentAffairsController@update_ca_day');
        Route::post('daily-ca/daily-ca-linktype-update', 'cms\CurrentAffairsController@update_ca_link');
        Route::post('daily-ca/daily-ca-pdftype-update', 'cms\CurrentAffairsController@update_ca_pdf');

        Route::resource('daily-ca', 'cms\CurrentAffairsController');

        Route::get('free-class/delete/{id}', 'cms\ClassFreeController@delete')->name('free-class.delete');
        Route::post('free-class/update-freeclass', 'cms\ClassFreeController@update');
        Route::get('free-class-list', 'cms\ClassFreeController@list')->name('free-class.list');
        Route::post('free-class/free-class-csvtype', 'cms\ClassFreeController@freeclass_csv');
        Route::resource('free-class', 'cms\ClassFreeController');

        Route::get('paid-class/delete/{id}', 'cms\ClassPaidController@delete')->name('paid-class.delete');
        Route::post('paid-class/update-paidclass', 'cms\ClassPaidController@update');
        Route::post('paid-class/paid-class-csvtype', 'cms\ClassPaidController@paidclass_csv');
        Route::get('paid-class-list', 'cms\ClassPaidController@list')->name('paid-class.list');
        Route::resource('paid-class', 'cms\ClassPaidController');


        Route::get('free_category_class/delete/{id}', 'cms\CategoryFreeController@delete')->name('free_category.delete');

        Route::post('free_category_class/update-freecategory', 'cms\CategoryFreeController@update');
        Route::resource('free_category_class', 'cms\CategoryFreeController');

        Route::get('free_subcategory_class/delete/{id}', 'cms\SubcategoryFreeController@delete')->name('free_subcategory.delete');

        Route::post('free_subcategory_class/update-freesubcategory', 'cms\SubcategoryFreeController@update');
        Route::resource('free_subcategory_class', 'cms\SubcategoryFreeController');


        Route::get('paid_category_class/delete/{id}', 'cms\CategoryPaidController@delete')->name('paid_category.delete');

        Route::post('paid_category_class/update-freecategory', 'cms\CategoryPaidController@update');
        Route::resource('paid_category_class', 'cms\CategoryPaidController');

        Route::get('paid_subcategory_class/delete/{id}', 'cms\SubcategoryPaidController@delete')->name('paid_subcategory.delete');

        Route::post('paid_subcategory_class/update-freesubcategory', 'cms\SubcategoryPaidController@update');
        Route::resource('paid_subcategory_class', 'cms\SubcategoryPaidController');



        Route::get('time-table/delete/{id}', 'cms\TimetableController@delete')->name('time-table.delete');
        Route::post('time-table/update-time-table', 'cms\TimetableController@update_timetable');

        Route::resource('time-table', 'cms\TimetableController');



        Route::get('today-bucket', 'cms\TodayBucketController@index')->name('daily-buckets.index');

        Route::post('today-bucket', 'cms\TodayBucketController@store');

        Route::get('today-bucket/show/{id}', 'cms\TodayBucketController@show');




        Route::get('today-bucket/edit/{id}', 'cms\TodayBucketController@edit');

        Route::post('today-bucket/edit/{id}', 'cms\TodayBucketController@update');

        Route::get('today-bucket/delete/{id}', 'cms\TodayBucketController@delete')->name('daily-buckets.delete');

        Route::get('daily-exam', 'cms\DailyexamController@daily_exams');
        Route::get('daily-exam/view', 'cms\DailyexamController@daily_exams_show');
        Route::get('daily-exam/date_based', 'cms\DailyexamController@daily_exams_date_based');
        Route::post('daily-exam/store', 'cms\DailyexamController@daily_exams_store');
        Route::post('daily-exam/edit-details', 'cms\DailyexamController@exam_details_update');
        Route::get('daily-exam/edit/{id}', 'cms\DailyexamController@daily_exams_edit');
        Route::post('daily-exam/update/{id}', 'cms\DailyexamController@daily_exams_update');
        Route::get('daily-exam/question-edit', 'cms\DailyexamController@questionEdit');
        Route::get('daily-exam/question-delete', 'cms\DailyexamController@questionDelete');
        Route::post('daily-exam/save-question/{id}', 'cms\DailyexamController@save_question');
        Route::post('daily-exam/update-question/{id}', 'cms\DailyexamController@update_question');
        Route::get('daily-exam/delete/{id}', 'cms\DailyexamController@daily_exams_delete');
        Route::get('daily-exam/delete-all/{id}', 'cms\DailyexamController@daily_exams_delete_all');
        Route::get('daily-exam/add-weekly-monthly-exam', 'cms\DailyexamController@daily_exams_delete_all');
        Route::get('daily-exam/date_depend_subjects', 'cms\DailyexamController@date_depend_subjects');
        Route::get('daily-exam/clear-leaderboard/{id}', 'cms\DailyexamController@clear_leaderboard');

        Route::get('ca-daily-exam', 'cms\CaDailyexamController@daily_exams');
        Route::get('ca-daily-exam/view', 'cms\CaDailyexamController@daily_exams_show');
        Route::get('ca-daily-exam/date_based', 'cms\CaDailyexamController@daily_exams_date_based');
        Route::post('ca-daily-exam/store', 'cms\CaDailyexamController@daily_exams_store');
        Route::post('ca-daily-exam/edit-details', 'cms\CaDailyexamController@exam_details_update');
        Route::get('ca-daily-exam/edit/{id}', 'cms\CaDailyexamController@daily_exams_edit');
        Route::post('ca-daily-exam/update/{id}', 'cms\CaDailyexamController@daily_exams_update');
        Route::get('ca-daily-exam/question-edit', 'cms\CaDailyexamController@questionEdit');
        Route::get('ca-daily-exam/question-delete', 'cms\CaDailyexamController@questionDelete');
        Route::post('ca-daily-exam/save-question/{id}', 'cms\CaDailyexamController@save_question');
        Route::post('ca-daily-exam/update-question/{id}', 'cms\CaDailyexamController@update_question');
        Route::get('ca-daily-exam/delete/{id}', 'cms\CaDailyexamController@daily_exams_delete');
        Route::get('ca-daily-exam/delete-all/{id}', 'cms\CaDailyexamController@daily_exams_delete_all');
        Route::get('ca-daily-exam/add-weekly-monthly-exam', 'cms\CaDailyexamController@daily_exams_delete_all');
        Route::get('ca-daily-exam/date_depend_subjects', 'cms\CaDailyexamController@date_depend_subjects');
        Route::get('ca-daily-exam/clear-leaderboard/{id}', 'cms\CaDailyexamController@clear_leaderboard');



        Route::get('model-exam', 'cms\ModelExamController@model_exams');
        Route::get('model-exam/view', 'cms\ModelExamController@model_exams_show');
        Route::get('model-exam/date_based', 'cms\ModelExamController@model_exams_date_based');
        Route::post('model-exam/store', 'cms\ModelExamController@model_exams_store');
        Route::post('model-exam/edit-details', 'cms\ModelExamController@exam_details_update');

        Route::post('model-exam/update/{id}', 'cms\ModelExamController@model_exams_update');
        Route::get('model-exam/delete/{id}', 'cms\ModelExamController@model_exams_delete');
        Route::get('model-exam/delete-all/{id}', 'cms\ModelExamController@model_exams_delete_all');
        Route::get('model-exam/add-weekly-monthly-exam', 'cms\ModelExamController@model_exams_delete_all');
        Route::get('model-exam/date_depend_subjects', 'cms\ModelExamController@date_depend_subjects');
        Route::get('model-exam/clear-leaderboard/{id}', 'cms\ModelExamController@clear_leaderboard');
        Route::get('model-exam/edit/{id}', 'cms\ModelExamController@model_exams_edit');

        Route::get('subjects', 'cms\SubjectController@subjects_all')->name('subjects.index');
        Route::post('subjects/create', 'cms\SubjectController@subject_store');
        Route::get('subjects/edit/{id}', 'cms\SubjectController@subject_edit')->name('subjects.edit');
        Route::post('subjects/update/{id}', 'cms\SubjectController@subject_update')->name('subjects.update');
        Route::get('subjects/delete/{id}', 'cms\SubjectController@subject_delete')->name('subjects.delete');



        Route::get('sub-subjects', 'cms\SubjectController@sub_subjects_all')->name('sub-subjects.index');
        Route::post('sub-subjects/create', 'cms\SubjectController@sub_subject_store');
        Route::get('sub-subjects/edit/{id}', 'cms\SubjectController@sub_subject_edit')->name('sub-subjects.edit');
        Route::post('sub-subjects/update/{id}', 'cms\SubjectController@sub_subject_update')->name('sub-subjects.update');
        Route::get('sub-subjects/delete/{id}', 'cms\SubjectController@sub_subject_delete')->name('sub-subjects.delete');
    });
});
