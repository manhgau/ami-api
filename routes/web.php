<?php

use Illuminate\Support\Facades\Route;
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
/*Route::fallback(function () {
    return response()->json([
        'message' => 'Unknown error',
        'code'    =>  405,
        'data'    =>  []
    ], 405);
});*/

Route::get('/',function(){return response()->json([
    'message' => 'Servey API...',
    'code'    =>  200,
    'data'    =>  []
], 200);});


