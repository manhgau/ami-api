<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\client\AuthController;
use App\Http\Controllers\v1\partner\AuthController as PartnerAuthController;
use App\Http\Controllers\v1\client\ConfigController as  ClientConfigController;
use App\Http\Controllers\v1\partner\ConfigController as PartnerConfigController;
use App\Http\Controllers\v1\visitor\BlogCategoryController;
use App\Http\Controllers\v1\visitor\BlogController;

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
    Route::group([], function ($router) {
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
            Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
            Route::post('/reset-password', [AuthController::class, 'resetPassword']);

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
            Route::post('/forgot-password', [PartnerAuthController::class, 'forgotPassword']);
            Route::post('/reset-password', [PartnerAuthController::class, 'resetPassword']);

            Route::group([
                'middleware' => 'partner_auth',

            ], function ($router) {
                Route::post('/logout', [PartnerAuthController::class, 'logout']);
                Route::post('/refresh', [PartnerAuthController::class, 'refresh']);
                Route::get('/profile', [PartnerAuthController::class, 'profile']);
                Route::post('/change-password', [PartnerAuthController::class, 'changePassWord']);
            });
        });
        //end auth
    });
    //END partner (app)

    //visitor (web)
    Route::group([
        'prefix' => 'visitor'
    ], function ($router) {
        Route::group([
            'prefix' => 'blog'

        ], function ($router) {
            Route::get('blog-category', [BlogCategoryController::class, 'getAll']);
            Route::get('blog-category/{id}', [BlogCategoryController::class, 'getDetail']);
            Route::get('get-list', [BlogController::class, 'getAll']);
            Route::get('blog-relate/{slug}', [BlogController::class, 'getBlogRelate']);
            Route::get('get-detail/{slug}', [BlogController::class, 'getDetail']);
        });
    });
    //END visitor (web)

    //client (app)
    Route::group([
        'prefix' => 'client-app'
    ], function ($router) {
    });
    //END client (app)
});
