<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

//Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//    return $request->user();
//});

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['basic.auth'])->get('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login']);
Route::middleware(['basic.auth'])->get('/signup', [\App\Http\Controllers\Admin\AuthController::class, 'login']);
Route::middleware(['basic.auth'])->get('/verify-account', [\App\Http\Controllers\Admin\AuthController::class, 'verifyAccount']);


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
*/


