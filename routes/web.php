<?php

use Illuminate\Support\Facades\Route;
use  App\Http\Controllers;


/* Route::get("/world-cup-prediction", function(){

        $title = "World Cup 2022 Winner Prediction" ;
        $redirection = redirect()->back();
  
        return view('wc-predition',compact('title','redirection')); 
    });
*/

  Route::get("app", function(){
       return view("app.index");
    });
    
    
Route::post('/send-otp','MobileNumberController@sendOTP');
Route::post('/verify-otp', 'MobileNumberController@verifyOTP');

Route::post('mobile-number-verification','MobileNumberController@mobileNoVerification');

    Route::get('account-settings', 'UserController@account_settings')->name('account-settings');
    Route::post('account-settings', 'UserController@account_settings_submit')->name('account-settings-submit');


    // Route::get('/', 'HomeController@index');
    Route::get('/', 'cms\HomeController@homepage');
    
    // Route::get("new-home", function(){
    //   return view("test.new-home");
    // });
    Route::get('new-home', 'HomeController@index');
    
    Route::get("/chat-sample", function(){
       return view("test.index");
    });
    
    Route::get("sign-in", function(){
        if(\auth()->check()){
          return redirect('/');
        }
       return view("kpsc.sample-login-page");
    })->name('sign-in');

    Route::get("sign-in", function(){
        if(\auth()->check()){
          return redirect('/');
        }
       return view("kpsc.sample-login-page");
    })->name('login');
    

    Route::get("sign-out", function(){
         \Auth::logout();
       return redirect()->back();
    })->name('sign-out');



    
    
    Route::get('feedback/{author}', 'HomeController@feedback_form');
    
    Route::post('feedback/{author}', 'HomeController@feedback_submit');
    
    Route::get('info/{title}', 'HomeController@info');
    
    
    Route::get('/daily_ca', 'HomeController@daily_ca_get');
    Route::post('/daily_ca', 'HomeController@daily_ca_store');
    
    Route::get('/upload-pscbullet-in', 'HomeController@upload_pscbullet_in');
    Route::post('/upload-pscbullet-in', 'HomeController@upload_pscbullet_in_store');
    
    Route::post('ckeditor/upload', 'CkeditorController@upload')->name('ckeditor.upload');
    
    // Route::controller(LoginController::class)->group(function(){
        
    //     Route::get('sign-in', 'index')->name('sign-in.index');
    
    //     Route::post('sign-in', 'check')->name('sign-in.check');
    
    
    //     Route::get('sign-up', 'create')->name('sign-up.create');
    
    //     Route::post('sign-up', 'store')->name('sign-up.store');
    
    //     Route::get('sign-out', 'sign_out')->name('sign-out.sign_out');
    
    // });

Route::controller(UserController::class)->group(function(){
    
    Route::get('register', 'register')->name('register.register');

  

    Route::post('stage1', 'stage1_store')->name('stage1.create');

    Route::post('stage2', 'stage2_store')->name('stage2.store');

});


// Route::middleware(['auth:sanctum', 'verified'])->get('/dashboard', function () {
//     return view('dashboard');
// })->name('dashboard');
  
Route::controller(GoogleController::class)->group(function(){
    Route::get('auth/google', 'redirectToGoogle')->name('auth.google');
    Route::get('auth/google/callback', 'handleGoogleCallback');
});

Route::controller(FacebookController::class)->group(function(){
    Route::get('auth/facebook', 'redirectToFacebook')->name('auth.facebook');
    Route::get('auth/facebook/callback', 'handleFacebookCallback');
});




Route::group(['prefix'=>'admin','as'=>'admin','middleware' => ['auth']], function(){
    Route::get('/', 'admin\AdminController@dashboard')->name('admin.index');
    Route::get('dashboard', 'admin\AdminController@dashboard');

    Route::group(['prefix'=>'general','as'=>'general'], function(){
       
        Route::get('/', 'admin\AdminController@general_dashboard');
        Route::get('cms', 'admin\AdminController@general_dashboard');
        Route::resource('role', RoleController::class);
        
        Route::get('logs', 'General\LogController@index')->name('logs.index');
        Route::get('logs/destroy_all', 'General\LogController@destroy_all')->name('logs.destroy_all');
        Route::get('logs/{id}', 'General\LogController@show')->name('logs.show');
        Route::get('logs/{id}/destroy', 'General\LogController@destroy')->name('logs.delete');

        
        
        
        Route::get('users', 'General\UserController@users');
        Route::post('users', 'General\UserController@users_store');
        Route::get('users/delete/{id}', 'General\UserController@users_delete');
        Route::post('users/updating', 'General\UserController@users_updating');
        
        
        
        
        Route::get('feedback', 'General\AdminController@feedback');
        Route::post('feedback', 'General\AdminController@feedback_store');
        Route::get('feedback/delete/{id}', 'General\AdminController@feedback_delete');
        Route::post('feedback/updating', 'General\AdminController@feedback_updating');
        
        Route::get('feedback/view/{id}', 'General\AdminController@feedback_requests');
        

        Route::get('permission', 'General\PermissionController@permission_index');
        Route::post('permission', 'General\PermissionController@permission_store');
        Route::get('permission/delete/{id}', 'General\PermissionController@permission_delete');
        Route::post('permission/updating', 'General\PermissionController@permission_updating');
        
        Route::get('sever-cache-clear', 'General\UserController@sever_cache_clear');
        
        Route::get('pages', 'General\UserController@pages');
        
        Route::post('pages', 'General\UserController@pages_store');
        Route::post('update-pages', 'General\UserController@update_pages');
        Route::get('pages-delete/{id}', 'General\UserController@pages_delete');
        
        
        
    });

        
    Route::group(['prefix'=>'ug-pg','as'=>'ug-pg.'], function(){

    
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

    Route::group(['prefix'=>'kpsc','as'=>'kpsc.','middleware' => ['auth']], function(){

   
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
        Route::post('free-class/free-class-csvtype','cms\ClassFreeController@freeclass_csv');
        Route::resource('free-class', 'cms\ClassFreeController');

        Route::get('paid-class/delete/{id}', 'cms\ClassPaidController@delete')->name('paid-class.delete');
        Route::post('paid-class/update-paidclass', 'cms\ClassPaidController@update');
        Route::post('paid-class/paid-class-csvtype','cms\ClassPaidController@paidclass_csv');
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
        Route::get('daily-exam/delete/{id}', 'cms\DailyexamController@daily_exams_delete'); 
        Route::get('daily-exam/delete-all/{id}', 'cms\DailyexamController@daily_exams_delete_all'); 
        Route::get('daily-exam/add-weekly-monthly-exam', 'cms\DailyexamController@daily_exams_delete_all');
        Route::get('daily-exam/date_depend_subjects', 'cms\DailyexamController@date_depend_subjects');
        Route::get('daily-exam/clear-leaderboard/{id}', 'cms\DailyexamController@clear_leaderboard');

        
        
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
    
    Route::group(['prefix'=>'teaching','as'=>'teaching.'], function(){
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
    
    
    Route::group(['prefix'=>'students','as'=>'students'], function(){
        Route::get('/', 'student\HomeController@dashboard');
        Route::get('student-list','student\HomeController@students_list');
        Route::get('student-list/{slug}/delete','student\HomeController@student_delete');
         
    });
});

// ,'middleware' => ['auth']


    
    Route::post('sign-in', 'cms\HomeController@daily_exam_user_login')->name('sign-in-check');
    Route::post('sign-up', 'cms\HomeController@daily_exam_user_register')->name('sign-up-store');
    Route::post('kpsc/daily-exam/forget-password', 'cms\HomeController@daily_exam_forget_password');
    


    Route::group(['middleware' => ['auth']], function () { 
        Route::get('kpsc/register','LoginController@register_new');
        Route::post('kpsc/register','LoginController@register_submit');
        Route::get('kpsc/daily-exam/{date}/{id}', 'cms\HomeController@get_date_based_questions_new');
        Route::get('kpsc/daily-exam-new/{date}/{id}', 'cms\HomeController@get_date_based_questions');
        Route::post('kpsc/daily-exam-store-result/{date}/{id}', 'cms\HomeController@store_exam_results');
        
    });

Route::group(['prefix'=>'kpsc','as'=>'kpsc','middleware' => ['auth']], function(){
    Route::get('daily-exam/{date}/{id}/discussion', 'cms\HomeController@exam_discussion');
    Route::post('daily-exam/{date}/{id}/discussion', 'cms\HomeController@exam_discussion_store');
    Route::get('daily-exam/{date}/{id}/leaderboard', 'cms\HomeController@exam_leaderboard');
});

    Route::get('/kpsc', 'cms\HomeController@homepage');

    Route::get('/kpsc/home', 'cms\HomeController@homepage');
    
Route::group(['prefix'=>'kpsc','as'=>'kpsc'], function(){


    Route::get('daily-exam', 'cms\HomeController@get_daily_exam_dates');
    
    Route::get('model-exam', 'cms\HomeController@get_model_exam_dates');
    Route::get('model-exam/{date}/{id}', 'cms\HomeController@get_model_date_based_questions');
    Route::post('model-exam/store-result/{id}', 'cms\HomeController@store_model_exam_results');
    Route::get('model-exam/{date}/{id}/leaderboard', 'cms\HomeController@model_exam_leaderboard');
    Route::get('model-exam/{date}/{id}/answerkey', 'cms\HomeController@model_exam_answerkey');


    
    Route::get("model-exam-mark-form", function(){
       return view("kpsc.model-exam-mark-form");
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


Route::group(['prefix'=>'ug-pg','as'=>'ug-pg'], function(){
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


    Route::get('about-us', function(){
        return view('cms.about-us');
    });  

    Route::get('contact-us', function(){
        return view('cms.contact-us');
    });
    
    Route::get('faq', function(){
        return view('cms.faq');
    });


