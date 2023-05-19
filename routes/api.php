<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['namespace' => 'API'], function () {

    Route::post('login', 'AuthController@login');
    Route::post('register', 'AuthController@register');
    
    Route::group(['middleware' => ['auth:api']], function () {
        Route::post('news', 'NewsController@index')->name('news.index');
        Route::get('filter_parameters', 'SettingsController@getCategoriesAndSources');
        Route::get('my_filters', 'SettingsController@getMyFilters');
        Route::post('update_settings', 'SettingsController@updateSettings');
        Route::get('logout', 'AuthController@logout');
    });
});
