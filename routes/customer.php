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
    Route::get('/get-kyc-responses', [\App\Http\Controllers\Customer\CusboardingController::class, 'fetchCustomerKYCResponses']);

    Route::post('/upload-kyc-file', [\App\Http\Controllers\Customer\CusboardingController::class, 'uploadKYCFile'] );
    Route::post('/get-kyc-url-from-path', [\App\Http\Controllers\Customer\CusboardingController::class, 'getKYCUrlFromPath'] );

});

/*
|--------------------------------------------------------------------------
| Loan application Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['auth:sanctum', 'role:customer'])->group(function() {

    Route::post('/submit-loan-application', [\App\Http\Controllers\Customer\LoanApplicationController::class, 'submitApplication']);
    Route::get('/fetch-loan-application-update', [\App\Http\Controllers\Customer\LoanApplicationController::class, 'fetchLoanApplicationUpdate']);

    Route::get('/fetch-loan-repayment-data/{loanId}', [\App\Http\Controllers\Customer\LoanApplicationController::class, 'getLoanRepaymentData']);
    Route::get('/fetch-loan-deferment-data/{loanId}', [\App\Http\Controllers\Customer\LoanApplicationController::class, 'getLoanDefermentData']);

    Route::get('/fetch-loan-details/{loanId}', [\App\Http\Controllers\Customer\LoanApplicationController::class, 'getLoanDetails']);
    Route::get('/fetch-loan-history', [\App\Http\Controllers\Customer\LoanApplicationController::class, 'fetchLoanApplicationHistory']);

    Route::get('/initiate-loan-repayment', [\App\Http\Controllers\Customer\LoanApplicationController::class, 'initiateLoanRepayment']);
    Route::get('/initiate-loan-deferment', [\App\Http\Controllers\Customer\LoanApplicationController::class, 'initiateLoanDeferment']);

});



/*
|--------------------------------------------------------------------------
| User personal info Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['auth:sanctum', 'role:customer'])->group(function() {

    Route::post('/submit-agreement-status', [\App\Http\Controllers\Customer\UserController::class, 'submitAgreementStatus']);
    Route::post('/submit-customer-call-logs', [\App\Http\Controllers\Customer\UserController::class, 'submitCallLogs']);
    Route::post('/submit-customer-location-logs', [\App\Http\Controllers\Customer\UserController::class, 'submitLocation']);

});
