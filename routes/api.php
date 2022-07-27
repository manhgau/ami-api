<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\client\AuthController;
use App\Http\Controllers\v1\partner\AuthController as PartnerAuthController;
use App\Http\Controllers\v1\client\ConfigController as  ClientConfigController;
use App\Http\Controllers\v1\partner\ConfigController as PartnerConfigController;
use App\Http\Controllers\v1\visitor\AcademicLevelCotroller;
use App\Http\Controllers\v1\visitor\BlogCategoryController;
use App\Http\Controllers\v1\visitor\BlogController;
use App\Http\Controllers\v1\visitor\DistrictController;
use App\Http\Controllers\v1\visitor\JobStatusController;
use App\Http\Controllers\v1\visitor\JobTypeCotroller;
use App\Http\Controllers\v1\visitor\PageController;
use App\Http\Controllers\v1\visitor\ProvinceController;
use App\Http\Controllers\v1\visitor\QAndACategoryController;
use App\Http\Controllers\v1\visitor\QAndAController;
use App\Http\Controllers\v1\visitor\WardController;

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
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::post('/register', [AuthController::class, 'register']);
            Route::post('/active-by-email', [AuthController::class, 'activeByEmail']);
            Route::post('/resend-active-email', [AuthController::class, 'resendActiveEmail']);
            Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
            Route::post('/reset-password', [AuthController::class, 'resetPassword']);

            Route::group([
                'middleware' => 'client_auth',

            ], function ($router) {
                Route::post('/logout', [AuthController::class, 'logout']);
                Route::get('/profile', [AuthController::class, 'profile']);
                Route::post('/change-pass', [AuthController::class, 'changePassWord']);
            });
        });
        //END auth
        //required login
        Route::group([
            'middleware' => 'client_auth',

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
            Route::post('/refresh', [PartnerAuthController::class, 'refresh']);
            Route::post('/check-register', [PartnerAuthController::class, 'checkRegister']);
            Route::post('/register', [PartnerAuthController::class, 'register']);
            Route::post('/forgot-password', [PartnerAuthController::class, 'forgotPassword']);
            Route::post('/reset-password', [PartnerAuthController::class, 'resetPassword']);

            Route::group([
                'middleware' => 'partner_auth',

            ], function ($router) {
                Route::post('/logout', [PartnerAuthController::class, 'logout']);
                Route::get('/profile', [PartnerAuthController::class, 'profile']);
                Route::post('/change-password', [PartnerAuthController::class, 'changePassWord']);
            });
        });
        //end auth
        //required login
        Route::group([
            'middleware' => 'partner_auth',

        ], function ($router) {
        });
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
        Route::group([
            'prefix' => 'qa'

        ], function ($router) {
            Route::get('qa-category', [QAndACategoryController::class, 'getAll']);
            Route::get('qa-category/{id}', [QAndACategoryController::class, 'getDetail']);
            Route::get('get-list', [QAndAController::class, 'getAll']);
            Route::get('qa-relate/{slug}', [QAndAController::class, 'getQAndARelate']);
            Route::get('get-detail/{slug}', [QAndAController::class, 'getDetail']);
        });
        Route::group([
            'prefix' => 'page'

        ], function ($router) {
            Route::get('get-list', [PageController::class, 'getAll']);
            Route::get('get-detail/{slug}', [PageController::class, 'getDetail']);
        });
        Route::get('get-province', [ProvinceController::class, 'getProvince']);
        Route::get('get-district/{province_code}', [DistrictController::class, 'getDistrict']);
        Route::get('get-ward/{district_code}', [WardController::class, 'getWard']);
        //
        Route::get('get-job-status', [JobStatusController::class, 'getJobStatus']);
        Route::get('get-job-type', [JobTypeCotroller::class, 'getJobType']);
        Route::get('get-academic-level', [AcademicLevelCotroller::class, 'getAcademicLevel']);
    });
    //END visitor (web)

    //client (app)
    Route::group([
        'prefix' => 'client-app'
    ], function ($router) {
    });
    //END client (app)
});
