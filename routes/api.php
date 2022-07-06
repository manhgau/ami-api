<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\v1\client\AuthController;

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
    //client
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
    //partner
});
