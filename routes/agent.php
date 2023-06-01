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

Route::middleware(['basic.auth'])->post('/login', [\App\Http\Controllers\Agent\AuthController::class, 'loginAsAgent']);
Route::middleware(['basic.auth'])->post('/request-password-reset', [\App\Http\Controllers\Agent\AuthController::class, 'requestAdminPasswordReset']);
Route::middleware(['auth:sanctum'])->post('/set-password-auth', [\App\Http\Controllers\Agent\AuthController::class, 'setPassword']);

Route::middleware(['auth:sanctum', 'role:agent'])->get('/get-initial-data', [\App\Http\Controllers\Agent\AuthController::class, 'getInitialData']);

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
*/






