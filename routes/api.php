<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\client\AuthController;
use App\Http\Controllers\v1\partner\AuthController as PartnerAuthController;
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
    //END common

    //client (web)
    Route::group([
        'prefix' => 'client'

    ], function ($router) {
        //
        Route::get('/settings', [ClientConfigController::class, 'settings']);
        //auth
        Route::group([
            'prefix' => 'auth'

        ], function ($router) {
            Route::post('/login', [AuthController::class, 'login']);
            Route::post('/register', [AuthController::class, 'register']);
            Route::post('/active-by-email', [AuthController::class, 'activeByEmail']);
            Route::post('/resend-active-email', [AuthController::class, 'resendActiveEmail']);
            Route::post('/reset-password', [AuthController::class, 'resetPassword']);
            Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

            Route::group([
                'middleware' => 'api',

            ], function ($router) {
                Route::post('/logout', [AuthController::class, 'logout']);
                Route::post('/refresh', [AuthController::class, 'refresh']);
                Route::get('/profile', [AuthController::class, 'profile']);
                Route::post('/change-pass', [AuthController::class, 'changePassWord']);
            });
        });
        //END auth
        //required login
        Route::group([
            'middleware' => 'api',

        ], function ($router) {

        });
    });
    //END client (web)

    //partner (app)
    Route::group([
        'prefix' => 'partner'
    ], function ($router) {
        Route::get('/settings', [PartnerConfigController::class, 'settings']);
        //auth
        Route::group([
            'prefix' => 'auth'

        ], function ($router) {
            Route::post('/login', [PartnerAuthController::class, 'login']);
            Route::post('/check-register', [PartnerAuthController::class, 'checkRegister']);
            Route::post('/register', [PartnerAuthController::class, 'register']);

            Route::group([
                'middleware' => 'partner_auth',

            ], function ($router) {
                Route::post('/logout', [PartnerAuthController::class, 'logout']);
                Route::get('/profile', [PartnerAuthController::class, 'profile']);
            });
        });
        //end auth
    });
    //END partner (app)

    //visitor (web)
    Route::group([
        'prefix' => 'partner'
    ], function ($router) {

    });
    //END visitor (web)

    //client (app)
    Route::group([
        'prefix' => 'client-app'
    ], function ($router) {

    });
    //END client (app)
});
