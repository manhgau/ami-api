<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\client\AuthController;
use App\Http\Controllers\v1\client\ConfigController as  ClientConfigController;
use App\Http\Controllers\v1\partner\ConfigController as PartnerConfigController;

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

//
Route::group(['prefix' => 'v1'], function () {
    //common
    Route::group([

    ], function ($router) {

    });
    //client (web)
    Route::group([
        'prefix' => 'client'

    ], function ($router) {
        //
        Route::get('/settings', [ClientConfigController::class, 'settings']);
        Route::get('/test-email', [ClientConfigController::class, 'testEmail']);
        //auth
        Route::group([
            'prefix' => 'auth'

        ], function ($router) {
            Route::post('/login', [AuthController::class, 'login']);
            Route::post('/register', [AuthController::class, 'register']);

            Route::group([
                'middleware' => 'api',

            ], function ($router) {
                Route::post('/logout', [AuthController::class, 'logout']);
                Route::post('/refresh', [AuthController::class, 'refresh']);
                Route::get('/user-profile', [AuthController::class, 'userProfile']);
                Route::post('/change-pass', [AuthController::class, 'changePassWord']);
            });
        });
        //required login
        Route::group([
            'middleware' => 'api',

        ], function ($router) {

        });
    });
    //partner (app)
    Route::group([
        'prefix' => 'partner'
    ], function ($router) {
        Route::get('/settings', [PartnerConfigController::class, 'settings']);
    });
    //visitor (web)
    Route::group([
        'prefix' => 'partner'
    ], function ($router) {

    });
    //client (app)
    Route::group([
        'prefix' => 'client-app'
    ], function ($router) {

    });
});
