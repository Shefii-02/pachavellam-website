<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('kpsc/daily-exam/forget-password', 'cms\HomeController@daily_exam_forget_password');



Route::group(['middleware' => ['auth']], function () {
    Route::get('kpsc/register', 'LoginController@register_new');
    Route::post('kpsc/register', 'LoginController@register_submit');
    Route::get('kpsc/daily-exam/{date}/{id}', 'cms\HomeController@get_date_based_questions_new');
    Route::get('kpsc/daily-exam-new/{date}/{id}', 'cms\HomeController@get_date_based_questions');
    Route::post('kpsc/daily-exam-store-result/{date}/{id}', 'cms\HomeController@store_exam_results');
});

Route::group(['prefix' => 'kpsc', 'as' => 'kpsc', 'middleware' => ['auth']], function () {
    Route::get('daily-exam/{date}/{id}/discussion', 'cms\HomeController@exam_discussion');
    Route::post('daily-exam/{date}/{id}/discussion', 'cms\HomeController@exam_discussion_store');
    Route::get('daily-exam/{date}/{id}/leaderboard', 'cms\HomeController@exam_leaderboard');
});


Route::group(['prefix' => 'kpsc', 'as' => 'kpsc'], function () {


    Route::get('daily-exam', 'cms\HomeController@get_daily_exam_dates');

    Route::get('model-exam', 'cms\HomeController@get_model_exam_dates');
    Route::get('model-exam/{date}/{id}', 'cms\HomeController@get_model_date_based_questions');
    Route::post('model-exam/store-result/{id}', 'cms\HomeController@store_model_exam_results');
    Route::get('model-exam/{date}/{id}/leaderboard', 'cms\HomeController@model_exam_leaderboard');
    Route::get('model-exam/{date}/{id}/answerkey', 'cms\HomeController@model_exam_answerkey');



    Route::get("model-exam-mark-form", function (Request $request) {
        $id = $request->id;
        return view("kpsc.model-exam-mark-form", compact('id'));
    });


    Route::get('home-new', 'cms\HomeController@homepage_new');

    Route::get('subject/{category_name}', 'cms\HomeController@subject_category_related');

    Route::get('subject/{category_name}/free-class', 'cms\HomeController@subject_related_free_class');
    Route::get('subject/{category_name}/{subcategory_name}/free-class', 'cms\HomeController@sub_subject_related_free_class');

    Route::get('subject/{category_name}/premium-class', 'cms\HomeController@subject_related_premium_class');
    Route::get('subject/{category_name}/{subcategory_name}/premium-class', 'cms\HomeController@sub_subject_related_premium_class');


    // youtube-class

    Route::get('subject/{category_name}/youtube-class', 'cms\HomeController@subject_related_youtube_class');
    Route::get('subject/{category_name}/{subcategory_name}/youtube-class', 'cms\HomeController@sub_subject_related_youtube_class');
    // study-notes

    Route::get('subject/{category_name}/study-notes', 'cms\HomeController@subject_related_study_notes');
    Route::get('subject/{category_name}/{subcategory_name}/study-notes', 'cms\HomeController@sub_subject_related_study_notes');
    // 

    Route::get('subject/{category_name}/mock-test', 'cms\HomeController@subject_related_mock_test');
    Route::get('subject/{category_name}/{subcategory_name}/mock-test', 'cms\HomeController@sub_subject_related_mock_test');


    Route::get('subject/{category_name}/school-text-Books', 'cms\HomeController@subject_related_text_books');
    Route::get('subject/{category_name}/{subcategory_name}/school-text-Books', 'cms\HomeController@sub_subject_related_text_books');


    Route::get('subject/{category_name}/{subcategory_name}', 'cms\HomeController@sub_subject_category_related');

    Route::get('daily-bucket', 'cms\HomeController@daily_bucket');

    Route::get('daily-bucket/{date}', 'cms\HomeController@daily_bucket_single');

    Route::get('daily-bucket/pdf/{date}/{pdf_name}', 'cms\HomeController@daily_bucket_show_pdf');
    // Route::get('daily-bucket/{date}', 'cms\HomeController@daily_bucket_single'); 




    // Route::get("daily-buckets/pdf/{date}/{pdf_name}", function(){
    //   return view("kpsc.daily_bucket_show_pdf");
    // });



    Route::get('live-class', 'cms\HomeController@live_class');

    Route::get('explore', 'cms\HomeController@explore');

    Route::get('psc-bulletin', 'cms\HomeController@bulletin');
    Route::get('notify', 'cms\HomeController@notify');

    Route::get('free/{category_name}', 'cms\HomeController@freecategory_related');
    Route::get('free/sub/{subcategory_name}', 'cms\HomeController@freesubcategory_related');


    Route::get('premium/{category_name}', 'cms\HomeController@paidcategory_related');
    Route::get('premium/sub/{subcategory_name}', 'cms\HomeController@paidsubcategory_related');

    Route::get('psc-new-pattern', 'cms\HomeController@question_answer');
    Route::get('psc-new-pattern/{id}', 'cms\HomeController@question_answer_id');
    Route::get('new-modal-qa-check-answer', 'cms\HomeController@new_modal_qa_check_answer');


    Route::get('psc-daily-current-affairs', 'cms\HomeController@daily_ca');
    Route::get('psc-daily-current-affairs/{year}', 'cms\HomeController@daily_ca_sub');
    Route::get('psc-daily-current-affairs/{year}/{month}', 'cms\HomeController@daily_ca_day');
    Route::get('psc-daily-current-affairs/{year}/{month}/{day}', 'cms\HomeController@daily_ca_single_day');

    Route::get('psc-material', 'cms\HomeController@psc_material');
    Route::get('psc-material/{sub_category}', 'cms\HomeController@psc_material_sub_category');


    Route::get('psc-syllabus/{sub_category}', 'cms\HomeController@psc_syllabus_sub_category');
    Route::get('psc-syllabus', 'cms\HomeController@psc_syllabus');

    Route::get('prev-qstn-ans', 'cms\HomeController@prev_qstn_ans');
    Route::get('prev-qstn-ans/{category}', 'cms\HomeController@prev_qstn_ans_sub_category');
    Route::get('prev-qstn-ans/{category}/{sub_category}', 'cms\HomeController@prev_qstn_ans_list');

    Route::get('psc-results/{sub_category}', 'cms\HomeController@psc_results_sub_category');
    Route::get('psc-results', 'cms\HomeController@psc_results');

    Route::get('psc-special-videos', 'cms\HomeController@psc_special_videos');

    Route::get('psc-our-videos', 'cms\HomeController@psc_our_videos');

    Route::get('news-all', 'cms\HomeController@news_all');
    Route::get('news/{id}', 'cms\HomeController@news_view');

    Route::get('feed', 'cms\HomeController@feed_show');
    Route::get('feed-comments', 'cms\HomeController@feed_comments_show');
    Route::get('feed-like', 'cms\HomeController@feed_like');
    Route::get('feed-comments-store', 'cms\HomeController@feed_comments_store');

    Route::get('time-table', 'cms\HomeController@time_tables');

    Route::get('psc-bulletin-downloading', 'cms\HomeController@psc_download_count');
});
