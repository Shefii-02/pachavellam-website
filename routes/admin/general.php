<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'admin', 'as' => 'admin', 'middleware' => ['auth']], function () {
    Route::get('/', 'admin\AdminController@dashboard')->name('admin.index');
    Route::get('dashboard', 'admin\AdminController@dashboard');

    Route::group(['prefix' => 'general', 'as' => 'general'], function () {

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
});
