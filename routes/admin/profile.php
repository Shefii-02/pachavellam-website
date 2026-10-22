<?php

use Illuminate\Support\Facades\Route;

// Profile of the logged in Admin / Admin Staff
Route::group(['prefix' => 'admin', 'middleware' => ['admin.area']], function () {
    Route::get('profile', 'admin\ProfileController@show')->name('admin.profile');
    Route::post('profile', 'admin\ProfileController@update');
    Route::post('profile/password', 'admin\ProfileController@password');
});

// the "Edit Profile" links used to point at /profile, which did not exist
Route::get('profile', fn () => redirect('admin/profile'));
