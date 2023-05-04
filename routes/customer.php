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


/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
|
*/
Route::middleware(['basic.auth'])->group(function () {

    Route::post('/login', [\App\Http\Controllers\Customer\AuthController::class, 'login']);
    Route::post('/register', [\App\Http\Controllers\Customer\AuthController::class, 'register']);
    Route::post('/verify-account', [\App\Http\Controllers\Customer\AuthController::class, 'verifyAccountOnSignup']);
    Route::post('/request-password-reset', [\App\Http\Controllers\Customer\AuthController::class, 'requestPasswordReset']);
    Route::post('/set-password', [\App\Http\Controllers\Customer\AuthController::class, 'setPassword']);

});
Route::middleware(['auth:sanctum', 'role:customer'])
    ->get('/get-initial-data', [\App\Http\Controllers\Customer\AuthController::class, 'getInitialData']);



/*
|--------------------------------------------------------------------------
| Cusboarding Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['auth:sanctum', 'role:customer'])->group(function() {

    Route::post('/set-kyc-responses', [\App\Http\Controllers\Customer\CusboardingController::class, 'setKYCResponses']);
    Route::post('/get-kyc-status', [\App\Http\Controllers\Customer\CusboardingController::class, 'fetchCustomerKYCStatus']);

});
