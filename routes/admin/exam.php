<?php

use Illuminate\Support\Facades\Route;

/*
 | Pages that are the same for every exam type, scoped by the exam type's slug:
 |   admin/exam/{exam}                    home (for exam types without their own module)
 |   admin/exam/{exam}/category           categories and sub categories
 |   admin/exam/{exam}/courses            courses, their content (folders, files, articles) and staff
 | Features, coupons and the exam types themselves are in General (routes/admin/general.php).
 */
Route::group(['prefix' => 'admin', 'middleware' => ['admin.area']], function () {

    Route::group(['prefix' => 'exam/{exam}', 'where' => ['exam' => '[a-z0-9\-]+'], 'middleware' => ['staff.can:access']], function () {

        Route::get('/', 'Exam\DashboardController@index');

        // exam series (daily / OMR / general knowledge) and subjects of this exam type
        Route::group(['prefix' => 'series/{kind}', 'where' => ['kind' => 'daily|omr|ca|gk|special']], function () {
            Route::get('/', 'Exam\ExamSeriesController@open');
            Route::post('seasons', 'Exam\ExamSeriesController@store');
            Route::get('seasons/{id}', 'Exam\ExamSeriesController@show')->whereNumber('id');
            Route::post('seasons/{id}/attach', 'Exam\ExamSeriesController@attach')->whereNumber('id');
            Route::post('seasons/{id}/place', 'Exam\ExamSeriesController@place')->whereNumber('id');
            Route::post('seasons/{id}/detach/{examId}', 'Exam\ExamSeriesController@detach')->whereNumber(['id', 'examId']);
            Route::post('seasons/{id}/free/{type}/{examId}', 'Exam\ExamSeriesController@free')->whereNumber(['id', 'examId'])->where('type', 'course|legacy');
            Route::post('seasons/{id}/legacy-status/{examId}', 'Exam\ExamSeriesController@legacyStatus')->whereNumber(['id', 'examId']);
            Route::post('seasons/{id}/move/{dir}', 'Exam\ExamSeriesController@move')->whereNumber('id')->where('dir', 'up|down');
            Route::put('seasons/{id}', 'Exam\ExamSeriesController@update')->whereNumber('id');
            Route::delete('seasons/{id}', 'Exam\ExamSeriesController@destroy')->whereNumber('id');
        });
        Route::get('subjects', 'Exam\ExamSubjectController@index');
        Route::post('subjects', 'Exam\ExamSubjectController@store');
        Route::put('subjects/{id}', 'Exam\ExamSubjectController@update')->whereNumber('id');
        Route::delete('subjects/{id}', 'Exam\ExamSubjectController@destroy')->whereNumber('id');

        Route::middleware('staff.can:Exam courses manage')->group(function () {
            // categories and sub categories (tree)
            Route::get('category', 'Exam\CategoryController@index');
            Route::get('category/create', 'Exam\CategoryController@create');
            Route::post('category', 'Exam\CategoryController@store');
            Route::get('category/{id}/edit', 'Exam\CategoryController@edit')->whereNumber('id');
            Route::put('category/{id}', 'Exam\CategoryController@update')->whereNumber('id');
            Route::delete('category/{id}', 'Exam\CategoryController@destroy')->whereNumber('id');

            // courses
            Route::get('courses', 'Exam\CourseController@index');
            Route::get('courses/create', 'Exam\CourseController@create');
            Route::post('courses', 'Exam\CourseController@store');
            Route::get('courses/{id}', 'Exam\CourseController@show')->whereNumber('id');
            Route::get('courses/{id}/edit', 'Exam\CourseController@edit')->whereNumber('id');
            Route::put('courses/{id}', 'Exam\CourseController@update')->whereNumber('id');
            Route::delete('courses/{id}', 'Exam\CourseController@destroy')->whereNumber('id');
            Route::post('courses/{id}/publish', 'Exam\CourseController@togglePublish')->whereNumber('id');

            // exams and related items a course takes from the existing banks
            // exams built inside the course (daily, OMR, special): settings, sections, questions from the question library
            Route::group(['prefix' => 'courses/{course}/exams/{feature}', 'where' => ['course' => '[0-9]+', 'id' => '[0-9]+', 'link' => '[0-9]+']], function () {
                Route::get('/', 'Exam\CourseExamController@index');
                Route::get('create', 'Exam\CourseExamController@create');
                Route::post('/', 'Exam\CourseExamController@store');
                Route::get('{id}', 'Exam\CourseExamController@show');
                Route::get('{id}/edit', 'Exam\CourseExamController@edit');
                Route::put('{id}', 'Exam\CourseExamController@update');
                Route::delete('{id}', 'Exam\CourseExamController@destroy');
                Route::post('{id}/status', 'Exam\CourseExamController@status');
                Route::post('{id}/copy', 'Exam\CourseExamController@copy');
                Route::get('{id}/library', 'Exam\CourseExamController@library');
                Route::post('{id}/questions', 'Exam\CourseExamController@addQuestions');
                Route::delete('{id}/questions/{link}', 'Exam\CourseExamController@removeQuestion');
                Route::post('{id}/questions/{link}/move', 'Exam\CourseExamController@moveQuestion');
            });
            Route::get('courses/{course}/related/{feature}', 'Exam\CourseItemController@show')->whereNumber('course');
            Route::post('courses/{course}/items/{feature}', 'Exam\CourseItemController@add')->whereNumber('course');
            Route::delete('courses/{course}/items/{feature}/{item}', 'Exam\CourseItemController@remove')->whereNumber(['course', 'item']);

            // course content: folders, files, articles
            Route::get('courses/{course}/content/{feature}', 'Exam\CourseContentController@index')->whereNumber('course');
            Route::get('courses/{course}/content/{feature}/create', 'Exam\CourseContentController@create')->whereNumber('course');
            Route::post('courses/{course}/content/{feature}', 'Exam\CourseContentController@store')->whereNumber('course');
            Route::post('courses/{course}/content/{feature}/starter', 'Exam\CourseContentController@starter')->whereNumber('course');
            Route::get('courses/{course}/content/{feature}/{node}/edit', 'Exam\CourseContentController@edit')->whereNumber(['course', 'node']);
            Route::put('courses/{course}/content/{feature}/{node}', 'Exam\CourseContentController@update')->whereNumber(['course', 'node']);
            Route::delete('courses/{course}/content/{feature}/{node}', 'Exam\CourseContentController@destroy')->whereNumber(['course', 'node']);

            // free / paid classes of a course: links to classes of the class library
            Route::group(['prefix' => 'courses/{course}/classes/{feature}', 'where' => ['course' => '[0-9]+', 'link' => '[0-9]+']], function () {
                Route::get('/', 'Exam\CourseClassController@index');
                Route::get('add', 'Exam\CourseClassController@pick');
                Route::post('add', 'Exam\CourseClassController@add');
                Route::post('quick', 'Exam\CourseClassController@quick');
                Route::put('{link}', 'Exam\CourseClassController@update');
                Route::delete('{link}', 'Exam\CourseClassController@remove');
                Route::post('{link}/move', 'Exam\CourseClassController@move');
            });
        });

        // class library: folders, classes (YouTube / CDN / cloud storage links)
        Route::group(['prefix' => 'class-library', 'where' => ['id' => '[0-9]+'], 'middleware' => ['staff.can:Class library manage']], function () {
            Route::get('/', 'Exam\ClassLibraryController@index');
            Route::post('folders', 'Exam\ClassLibraryController@storeFolder');
            Route::put('folders/{id}', 'Exam\ClassLibraryController@updateFolder');
            Route::delete('folders/{id}', 'Exam\ClassLibraryController@destroyFolder');
            Route::get('create', 'Exam\ClassLibraryController@create');
            Route::post('/', 'Exam\ClassLibraryController@store');
            Route::post('bulk', 'Exam\ClassLibraryController@bulk');
            Route::post('sync-legacy', 'Exam\ClassLibraryController@syncLegacy');
            Route::get('storage', 'Exam\ClassLibraryController@storage');
            Route::post('storage/import', 'Exam\ClassLibraryController@importFromStorage');
            Route::get('{id}/edit', 'Exam\ClassLibraryController@edit');
            Route::put('{id}', 'Exam\ClassLibraryController@update');
            Route::delete('{id}', 'Exam\ClassLibraryController@destroy');
        });

        // question library: folders, questions, imports
        Route::group(['prefix' => 'question-library', 'middleware' => ['staff.can:Question library manage']], function () {
            Route::get('/', 'Exam\QuestionLibraryController@index');
            Route::post('sync-daily-exams', 'Exam\QuestionLibraryController@syncDailyExams');
            Route::post('folders', 'Exam\QuestionLibraryController@storeFolder');
            Route::put('folders/{id}', 'Exam\QuestionLibraryController@updateFolder')->whereNumber('id');
            Route::delete('folders/{id}', 'Exam\QuestionLibraryController@destroyFolder')->whereNumber('id');

            Route::get('questions/create', 'Exam\QuestionLibraryController@createQuestion');
            Route::post('questions', 'Exam\QuestionLibraryController@storeQuestion');
            Route::post('questions/bulk', 'Exam\QuestionLibraryController@bulk');
            Route::get('questions/{id}/edit', 'Exam\QuestionLibraryController@editQuestion')->whereNumber('id');
            Route::put('questions/{id}', 'Exam\QuestionLibraryController@updateQuestion')->whereNumber('id');
            Route::delete('questions/{id}', 'Exam\QuestionLibraryController@destroyQuestion')->whereNumber('id');

            Route::get('import', 'Exam\QuestionImportController@create');
            Route::post('import', 'Exam\QuestionImportController@store');
            Route::get('imports', 'Exam\QuestionImportController@history');
            Route::get('imports/{id}', 'Exam\QuestionImportController@show')->whereNumber('id');
            Route::post('imports/{id}/undo', 'Exam\QuestionImportController@undo')->whereNumber('id');
            Route::get('samples/{ext}/{key}', 'Exam\QuestionImportController@sample');
        });
    });

    // coupons: for every exam type, in the main sidebar (Admin only - checked in the controller)
    Route::get('coupons', 'General\CouponController@index');
    Route::get('coupons/analytics', 'General\CouponController@analytics');
    Route::get('coupons/create', 'General\CouponController@create');
    Route::post('coupons', 'General\CouponController@store');
    Route::get('coupons/{id}', 'General\CouponController@show')->whereNumber('id');
    Route::get('coupons/{id}/edit', 'General\CouponController@edit')->whereNumber('id');
    Route::put('coupons/{id}', 'General\CouponController@update')->whereNumber('id');
    Route::delete('coupons/{id}', 'General\CouponController@destroy')->whereNumber('id');
    Route::post('coupons/{id}/toggle', 'General\CouponController@toggle')->whereNumber('id');
    Route::post('coupons/{id}/duplicate', 'General\CouponController@duplicate')->whereNumber('id');
    Route::post('coupons/{id}/redeem', 'General\CouponController@redeem')->whereNumber('id');
    Route::get('coupons/{id}/export', 'General\CouponController@export')->whereNumber('id');
    Route::post('coupons/redemptions/{id}/reverse', 'General\CouponController@reverse')->whereNumber('id');

    // Capsule and Stories: each is shown for the exam types ticked on it (permissions are checked in the controllers)
    Route::get('capsule', 'Content\CapsuleController@index');
    Route::get('capsule/create', 'Content\CapsuleController@create');
    Route::post('capsule', 'Content\CapsuleController@store');
    Route::get('capsule/{id}/edit', 'Content\CapsuleController@edit')->whereNumber('id');
    Route::put('capsule/{id}', 'Content\CapsuleController@update')->whereNumber('id');
    Route::delete('capsule/{id}', 'Content\CapsuleController@destroy')->whereNumber('id');
    Route::post('capsule/{id}/toggle', 'Content\CapsuleController@toggle')->whereNumber('id');

    Route::get('stories', 'Content\StoryController@index');
    Route::get('stories/create', 'Content\StoryController@create');
    Route::post('stories', 'Content\StoryController@store');
    Route::get('stories/{id}/edit', 'Content\StoryController@edit')->whereNumber('id');
    Route::put('stories/{id}', 'Content\StoryController@update')->whereNumber('id');
    Route::delete('stories/{id}', 'Content\StoryController@destroy')->whereNumber('id');
    Route::post('stories/{id}/toggle', 'Content\StoryController@toggle')->whereNumber('id');

    // custom pages (/p/{slug}) and prebuilt pages; menu setup
    Route::get('pages', 'Content\\SitePageController@index');
    Route::get('pages/create', 'Content\\SitePageController@create');
    Route::post('pages', 'Content\\SitePageController@store');
    Route::get('pages/{id}/edit', 'Content\\SitePageController@edit')->whereNumber('id');
    Route::put('pages/{id}', 'Content\\SitePageController@update')->whereNumber('id');
    Route::post('pages/{id}/toggle', 'Content\\SitePageController@toggle')->whereNumber('id');
    Route::delete('pages/{id}', 'Content\\SitePageController@destroy')->whereNumber('id');
    Route::get('devices', 'Content\\DeviceAdminController@index');
    Route::post('devices/settings', 'Content\\DeviceAdminController@settings');
    Route::post('devices/{id}/remove', 'Content\\DeviceAdminController@remove')->whereNumber('id');
    Route::post('devices/user/{id}/remove-all', 'Content\\DeviceAdminController@removeAll')->whereNumber('id');
    Route::post('devices/user/{id}/limit', 'Content\\DeviceAdminController@userLimit')->whereNumber('id');
    Route::get('security', 'Content\\SecurityController@index');
    Route::post('security/captcha', 'Content\\SecurityController@captcha');
    Route::post('security/limits', 'Content\\SecurityController@limits');
    Route::post('security/ips', 'Content\\SecurityController@addIp');
    Route::delete('security/ips/{id}', 'Content\\SecurityController@removeIp')->whereNumber('id');
    Route::get('ads', 'Content\\AdsController@index');
    Route::post('ads', 'Content\\AdsController@save');
    Route::get('branding', 'Content\\BrandingController@index');
    Route::post('branding', 'Content\\BrandingController@save');
    Route::get('menu', 'Content\\MenuItemController@index');
    Route::post('menu', 'Content\\MenuItemController@store');
    Route::put('menu/{id}', 'Content\\MenuItemController@update')->whereNumber('id');
    Route::post('menu/{id}/toggle', 'Content\\MenuItemController@toggle')->whereNumber('id');
    Route::delete('menu/{id}', 'Content\\MenuItemController@destroy')->whereNumber('id');

    Route::get('push', 'Content\\PushController@index');
    Route::post('push', 'Content\\PushController@send');
    Route::get('push/preview', 'Content\\PushController@preview');

    // Banners, What's New, Announcements, Articles, Notifications: common to all exam types (exam types chosen on each item)
    Route::get('content', fn () => redirect(url('admin/content/banners')));
    Route::prefix('content/{section}/folders')->where(['section' => 'free-classes|articles|syllabus|materials|results'])->group(function () {
        Route::post('/', 'Content\\ContentFolderController@store');
        Route::put('{id}', 'Content\\ContentFolderController@update')->whereNumber('id');
        Route::delete('{id}', 'Content\\ContentFolderController@destroy')->whereNumber('id');
    });
    Route::get('content/achievements', fn () => redirect(url('admin/stories')));
    Route::prefix('content/{section}')->where(['section' => 'free-classes|banners|whats-new|announcements|articles|notifications|syllabus|materials|results|achievements|site-stats|team|faqs|founder|footer-links|key-points|partners'])->group(function () {
        Route::get('/', 'Content\ContentItemController@index');
        Route::get('create', 'Content\ContentItemController@create');
        Route::get('import', 'Content\ContentItemController@importForm');
        Route::post('import', 'Content\ContentItemController@importStore');
        Route::post('/', 'Content\ContentItemController@store');
        Route::get('{id}/edit', 'Content\ContentItemController@edit')->whereNumber('id');
        Route::put('{id}', 'Content\ContentItemController@update')->whereNumber('id');
        Route::delete('{id}', 'Content\ContentItemController@destroy')->whereNumber('id');
        Route::post('{id}/toggle', 'Content\ContentItemController@toggle')->whereNumber('id');
    });

    // "Talk to a counsellor" requests from the home page
    Route::get('counselling', 'Content\CounsellingController@index');
    Route::put('counselling/{id}', 'Content\CounsellingController@update')->whereNumber('id');

    // Admissions & Revenue (Admin only - checked in the controllers)
    Route::group(['where' => ['id' => '[0-9]+']], function () {
        Route::get('admissions', 'Finance\AdmissionController@index');
        Route::get('admissions/create', 'Finance\AdmissionController@create');
        Route::get('admissions/students', 'Finance\AdmissionController@students');
        Route::get('admissions/export', 'Finance\AdmissionController@export');
        Route::post('admissions', 'Finance\AdmissionController@store');
        Route::get('admissions/{id}', 'Finance\AdmissionController@show');
        Route::post('admissions/{id}/cancel', 'Finance\AdmissionController@cancel');
        Route::post('admissions/{id}/payment', 'Finance\AdmissionController@recordPayment');
        Route::delete('admissions/{id}', 'Finance\AdmissionController@destroy');
        Route::get('revenue', 'Finance\RevenueController@index');
        Route::get('revenue/export', 'Finance\RevenueController@exportMonthly');
        Route::post('revenue/gateway', 'Finance\RevenueController@switchGateway');
        Route::get('payment-links', 'Finance\PaymentLinkController@index');
        Route::get('payment-links/create', 'Finance\PaymentLinkController@create');
        Route::post('payment-links', 'Finance\PaymentLinkController@store');
        Route::post('payment-links/{id}/cancel', 'Finance\PaymentLinkController@cancel');
        Route::get('transactions', 'Finance\RevenueController@transactionsIndex');
        Route::get('transactions/export', 'Finance\RevenueController@transactionsExport');
    });

    // Staff: access, activity log, notifications, new joins, trash
    Route::group(['where' => ['id' => '[0-9]+']], function () {
        Route::get('chat', 'Staff\ChatController@index');
        Route::get('staff-chat', fn (\Illuminate\Http\Request $r) => redirect(url('admin/chat') . ($r->getQueryString() ? '?' . $r->getQueryString() : '')));
        Route::get('chat/token', 'Staff\ChatController@token');
        // media library: all files in folders, upload, and the window every file field opens
        Route::get('media', 'Content\MediaController@index');
        Route::get('media/list', 'Content\MediaController@list');
        Route::post('media/upload', 'Content\MediaController@upload');
        Route::post('media/folders', 'Content\MediaController@folderStore');
        Route::put('media/folders/{id}', 'Content\MediaController@folderUpdate');
        Route::delete('media/folders/{id}', 'Content\MediaController@folderDestroy');
        Route::get('media/tree', 'Content\MediaController@treeJson');
        Route::post('media/folders/{id}/copy', 'Content\MediaController@folderCopy');
        Route::post('media/move', 'Content\MediaController@move');
        Route::post('media/copy', 'Content\MediaController@copy');
        Route::post('media/delete', 'Content\MediaController@destroy');
        Route::get('media/trash', 'Content\MediaController@trash');
        Route::post('media/restore', 'Content\MediaController@restore');
        Route::post('media/purge', 'Content\MediaController@purge');
        Route::put('media/{id}', 'Content\MediaController@update');
        // which exam series (Daily / OMR / GK / CA) of which exam type show on the home page
        Route::get('home-exams', 'Content\HomeExamsController@index');
        Route::post('home-exams', 'Content\HomeExamsController@save');
        // SEO: title / description / keywords / share image of each public page
        Route::get('seo', 'Staff\SeoController@index');
        Route::post('seo', 'Staff\SeoController@save');
        Route::post('seo/{key}/reset', 'Staff\SeoController@reset')->where('key', '[a-z0-9:\-]+');
        // video chats: the live chat beside free classes / special videos (class_chat_messages)
        Route::group(['prefix' => 'class-chats', 'where' => ['source' => 'item|video']], function () {
            Route::get('/', 'Staff\ClassChatAdminController@index');
            Route::get('{source}/{id}', 'Staff\ClassChatAdminController@show');
            Route::get('{source}/{id}/messages', 'Staff\ClassChatAdminController@messages');
            Route::post('{source}/{id}/send', 'Staff\ClassChatAdminController@send');
            Route::post('{source}/{id}/mode', 'Staff\ClassChatAdminController@mode');
            Route::post('{source}/{id}/messages/{message}/delete', 'Staff\ClassChatAdminController@destroy')->whereNumber('message');
            Route::post('{source}/{id}/users/{userId}/delete', 'Staff\ClassChatAdminController@destroyUser')->whereNumber('userId');
        });
        Route::post('impersonate/stop', 'Staff\ImpersonationController@stop');
        Route::post('impersonate/{id}', 'Staff\ImpersonationController@start');

        Route::get('staff-access', 'Staff\StaffAccessController@index');
        Route::get('staff-access/{id}/edit', 'Staff\StaffAccessController@edit');
        Route::put('staff-access/{id}', 'Staff\StaffAccessController@update');

        Route::get('activity-logs', 'Staff\ActivityLogController@index');
        Route::get('activity-logs/export', 'Staff\ActivityLogController@export');

        Route::get('notifications', 'Staff\NotificationController@index');
        Route::get('notifications/feed', 'Staff\NotificationController@feed');
        Route::post('notifications/read', 'Staff\NotificationController@read');
        Route::get('notifications/{id}/open', 'Staff\NotificationController@open');

        Route::get('new-joins', 'Staff\NewJoinController@index');
        Route::get('new-joins/export', 'Staff\NewJoinController@export');
        Route::post('new-joins/bulk', 'Staff\NewJoinController@bulk');
        Route::post('new-joins/{id}/mark', 'Staff\NewJoinController@mark');
        Route::delete('new-joins/{id}/mark', 'Staff\NewJoinController@unmark');

        Route::get('trash', 'Staff\TrashController@index');
        Route::post('trash/{type}/purge-old', 'Staff\TrashController@purgeOld')->where('type', '[a-z\-]+');
        Route::post('trash/{type}/{id}/restore', 'Staff\TrashController@restore')->where('type', '[a-z\-]+');
        Route::delete('trash/{type}/{id}', 'Staff\TrashController@destroy')->where('type', '[a-z\-]+');
    });

    // Roaring Study: timed study packs for one or more exam types, free for everyone or limited to courses.
    // Opened from an exam type (admin/exam/{exam}/roaring-study, inside that exam's sidebar) or as the all-exam list (admin/roaring-study).
    $roaringRoutes = function () {
        Route::get('/', 'Content\RoaringStudyController@index');
        Route::get('create', 'Content\RoaringStudyController@create');
        Route::post('/', 'Content\RoaringStudyController@store');
        Route::get('{id}', 'Content\RoaringStudyController@show');
        Route::get('{id}/edit', 'Content\RoaringStudyController@edit');
        Route::put('{id}', 'Content\RoaringStudyController@update');
        Route::delete('{id}', 'Content\RoaringStudyController@destroy');
        Route::post('{id}/status', 'Content\RoaringStudyController@status');
        Route::get('{id}/classes', 'Content\RoaringStudyController@classSearch');
        Route::post('{id}/sections', 'Content\RoaringStudyController@storeSection');
        Route::put('{id}/sections/{section}', 'Content\RoaringStudyController@updateSection');
        Route::delete('{id}/sections/{section}', 'Content\RoaringStudyController@destroySection');
        Route::post('{id}/sections/{section}/items', 'Content\RoaringStudyController@storeItem');
        Route::post('{id}/items/{item}/toggle', 'Content\RoaringStudyController@toggleItem');
        Route::post('{id}/items/{item}/move', 'Content\RoaringStudyController@moveItem');
        Route::delete('{id}/items/{item}', 'Content\RoaringStudyController@destroyItem');
    };
    $roaringWhere = ['id' => '[0-9]+', 'section' => '[0-9]+', 'item' => '[0-9]+'];
    Route::group(['prefix' => 'exam/{exam}/roaring-study', 'where' => $roaringWhere, 'middleware' => ['staff.can:Roaring study manage', \App\Http\Middleware\RoaringScope::class]], $roaringRoutes);
    Route::group(['prefix' => 'roaring-study', 'where' => $roaringWhere, 'middleware' => ['staff.can:Roaring study manage', \App\Http\Middleware\RoaringScope::class]], $roaringRoutes);
    foreach (['kpsc', 'rrb', 'ssc', 'ktet', 'ugc-net'] as $slug) {
        Route::get($slug . '/roaring-study', fn () => redirect('admin/exam/' . $slug . '/roaring-study'));
    }

    // old addresses (before categories / courses / coupons became common) keep working
    foreach (['kpsc', 'rrb', 'ssc', 'ktet', 'ugc-net'] as $slug) {
        Route::get($slug . '/category', fn () => redirect('admin/exam/' . $slug . '/category'));
    }
    Route::get('kpsc/courses', fn () => redirect('admin/exam/kpsc/courses'));
    Route::get('kpsc/coupons', fn () => redirect('admin/coupons?exam=kpsc'));
    Route::get('general/coupons', fn (\Illuminate\Http\Request $r) => redirect('admin/coupons' . ($r->getQueryString() ? '?' . $r->getQueryString() : '')));
    Route::get('general/coupons/{id}', fn ($id) => redirect('admin/coupons/' . $id))->whereNumber('id');
    Route::get('kpsc/features', fn () => redirect('admin/general/features'));
    Route::get('students/exams/create', fn () => redirect('admin/general/exam-types/create'));
});
