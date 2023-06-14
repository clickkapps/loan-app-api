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
| Loan related Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['auth:sanctum', 'role:agent'])->group(function() {

    Route::get('/get-loans-from-permissions', [\App\Http\Controllers\Agent\LoanApplicationController::class, 'getLoanApplicationsFromMyPermissions']);
    Route::get('/get-loan-stages-from-permissions', [\App\Http\Controllers\Agent\LoanApplicationController::class, 'getLoanStagesFromPermissions']);
    Route::get('/get-loan-selection-info', [\App\Http\Controllers\Agent\LoanApplicationController::class, 'getLoanSelectionInfo']);
    Route::post('/assign-loan-to-agent', [\App\Http\Controllers\Agent\LoanApplicationController::class, 'assignLoanToAgent']);
    Route::post('/un-assign-loan-to-agent', [\App\Http\Controllers\Agent\LoanApplicationController::class, 'unAssignLoanToAgent']);
    Route::get('/get-assigned-loans-by-category/{category}', [\App\Http\Controllers\Agent\LoanApplicationController::class, 'getLoansByCategories']);


    Route::get('/get-customer-kyc/{userId}', [\App\Http\Controllers\Agent\LoanApplicationController::class, 'getCustomerKYC']);

    Route::post('/add-follow-up-record', [\App\Http\Controllers\Agent\LoanApplicationController::class, 'addFollowUpRecord']);
    Route::get('/get-follow-up-records/{loanId}', [\App\Http\Controllers\Agent\LoanApplicationController::class, 'getFollowUpRecords']);

    Route::post('/add-follow-up-sms-log', [\App\Http\Controllers\Agent\LoanApplicationController::class, 'addFollowUpSmsLog']);
    Route::post('/add-follow-up-call-log', [\App\Http\Controllers\Agent\LoanApplicationController::class, 'addFollowUpCallLog']);
    Route::post('/add-follow-up-whatsapp-log', [\App\Http\Controllers\Agent\LoanApplicationController::class, 'addFollowUpWhatsappLog']);
    Route::get('/get-call-logs/{userId}', [\App\Http\Controllers\Agent\LoanApplicationController::class, 'getCustomerCallLogs']);





});





