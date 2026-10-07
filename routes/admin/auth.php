<?php

use Illuminate\Support\Facades\Route;

// Admin login (email + password). Only users.type "Admin" / "Admin Staff" can sign in here.
Route::prefix('admin')->group(function () {
    Route::get('login', 'admin\AdminLoginController@show')->name('admin.login');
    Route::post('login', 'admin\AdminLoginController@login')->middleware('throttle:10,1')->name('admin.login.submit');
    Route::post('logout', 'admin\AdminLoginController@logout')->name('admin.logout');
});
