<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


// Auth::routes();
Route::group(['namespace' => 'Auth'], function () {

    Route::get('login', 'LoginController@showLoginForm')->name('login');
    Route::post('login', 'LoginController@login');
    Route::post('logout', 'LoginController@logout')->name('logout');

    # Route::get('password/reset', 'ForgotPasswordController@showLinkRequestForm')->name('password.request');
    # Route::post('password/email', 'ForgotPasswordController@sendResetLinkEmail')->name('password.email');
    # Route::get('password/reset/{token}', 'ResetPasswordController@showResetForm')->name('password.reset');
    # Route::post('password/reset', 'ResetPasswordController@reset')->name('password.update');

    # Route::get('email/verify', 'VerificationController@show')->name('verification.notice');
    # Route::get('email/verify/{id}', 'VerificationController@verify')->name('verification.verify');
    # Route::get('email/resend', 'VerificationController@resend')->name('verification.resend');

});


Route::group(['namespace' => 'Page'], function () {

    Route::redirect('/', '/login', 301);

});


Route::group(['middleware' => ['auth', 'auth.ajax']], function () {

    Route::get('/home', 'Page\HomeController@index')->name('home');

});

