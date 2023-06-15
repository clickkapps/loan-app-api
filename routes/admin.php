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
Route::middleware(['basic.auth'])->post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login']);


/*
|--------------------------------------------------------------------------
| Admin management Routes
|--------------------------------------------------------------------------
|
*/
Route::middleware(['auth:sanctum', 'role:super admin|admin'])->group(function() {

    Route::post('/create-admin', [\App\Http\Controllers\Admin\AdminController::class, 'create']);
    Route::get('/get-admins', [\App\Http\Controllers\Admin\AdminController::class, 'getAll']);
    Route::get('/get-admin/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'getAdmin']);
    Route::post('/make-admin-an-agent', [\App\Http\Controllers\Admin\AdminController::class, 'makeAdminAnAgent']);
    Route::post('/unmake-admin-an-agent', [\App\Http\Controllers\Admin\AdminController::class, 'unmakeAdminAnAgent']);
    Route::get('/get-agents', [\App\Http\Controllers\Admin\AdminController::class, 'getAgents']);
    Route::get('/get-agent-assigned-loans', [\App\Http\Controllers\Admin\AdminController::class, 'getAgentAssignedLoans']);
    Route::get('/get-agent-info/{userId}', [\App\Http\Controllers\Admin\AdminController::class, 'getAgentInfo']);

    // what
    Route::post('/show-commissions/{userId?}', [\App\Http\Controllers\Admin\AdminController::class, 'showCommissions']);

});



/*
|--------------------------------------------------------------------------
| Permissions management Routes
|--------------------------------------------------------------------------
|
*/
Route::middleware(['auth:sanctum','role:super admin|admin'])->group(function() {

    Route::get('/get-permissions', [\App\Http\Controllers\Admin\RolePermissionController::class, 'getAll']);
    Route::get('/only-loan-stages-permissions', [\App\Http\Controllers\Admin\RolePermissionController::class, 'getLoanStagesPermissions']);
    Route::get('/get-user-permissions/{userId}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'getUserPermissions']);
    Route::post('/assign-permission', [\App\Http\Controllers\Admin\RolePermissionController::class, 'assign']);

});


/*
|--------------------------------------------------------------------------
| Admin config Routes
|--------------------------------------------------------------------------
|
*/
Route::middleware(['auth:sanctum', 'role:super admin|admin'])->group(function() {

    Route::post('/add-cusboarding-page', [\App\Http\Controllers\Admin\ConfigCusboardingController::class, 'addPage']);
    Route::post('/add-cusboarding-field', [\App\Http\Controllers\Admin\ConfigCusboardingController::class, 'addFieldToPage']);
    Route::post('/reassign-cusboarding-field-to-page', [\App\Http\Controllers\Admin\ConfigCusboardingController::class, 'reAssignFieldToPage']);
    Route::post('/reassign-cusboarding-field-to-position', [\App\Http\Controllers\Admin\ConfigCusboardingController::class, 'reAssignFieldToPosition']);
    Route::put('/update-field-values/{fieldId}', [\App\Http\Controllers\Admin\ConfigCusboardingController::class, 'updateFieldValues']);
    Route::delete('/delete-cusboarding-field/{id}', [\App\Http\Controllers\Admin\ConfigCusboardingController::class, 'removeField']);
    Route::delete('/delete-cusboarding-page/{id}', [\App\Http\Controllers\Admin\ConfigCusboardingController::class, 'removePage']);
    Route::get('/get-cusboarding-pages-fields', [\App\Http\Controllers\Admin\ConfigCusboardingController::class, 'getPagesWithFields']);


    Route::get('/get-general-configurations', [\App\Http\Controllers\Admin\GeneralConfigurationController::class, 'getConfigurations']);
    Route::put('/update-general-configurations/{id}', [\App\Http\Controllers\Admin\GeneralConfigurationController::class, 'updateConfigurations']);
    Route::get('/get-loan-stages-configurations', [\App\Http\Controllers\Admin\GeneralConfigurationController::class, 'getLoanStagesConfigurations']);
    Route::put('/update-loan-stages-configurations/{id}', [\App\Http\Controllers\Admin\GeneralConfigurationController::class, 'updateLoanStagesConfigurations']);
    Route::post('/add-loan-stages-configurations', [\App\Http\Controllers\Admin\GeneralConfigurationController::class, 'addLoanStagesConfigurations']);
    Route::delete('/delete-loan-stages-configurations/{id}', [\App\Http\Controllers\Admin\GeneralConfigurationController::class, 'removeLoanStagesConfiguration']);


    // commission configs
    Route::delete('/delete-loan-stages-configurations/{id}', [\App\Http\Controllers\Admin\GeneralConfigurationController::class, 'updateConfigurations']);

});


/*
|--------------------------------------------------------------------------
| Customers KYC Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['auth:sanctum', 'role:super admin|admin'])->group(function() {

    Route::get('/get-customers', [\App\Http\Controllers\Admin\CustomerController::class, 'getCustomers']);
    Route::get('/get-single-customer/{userId}', [\App\Http\Controllers\Admin\CustomerController::class, 'getCustomer']);
    Route::get('/get-single-customer-kyc/{userId}', [\App\Http\Controllers\Admin\CustomerController::class, 'getCustomerKYC']);

});



/*
|--------------------------------------------------------------------------
| Loan management Routes
|--------------------------------------------------------------------------
|
*/
Route::middleware(['auth:sanctum', 'role:super admin|admin'])->group(function() {

    Route::get('/fetch-loans/{type}', [\App\Http\Controllers\Admin\LoanApplicationController::class, 'fetchLoans']);
    Route::get('/fetch-loan-detail/{loanId}', [\App\Http\Controllers\Admin\LoanApplicationController::class, 'fetchLoanDetail']);
    Route::get('/fetch-loan-stages', [\App\Http\Controllers\Admin\LoanApplicationController::class, 'fetchLoanStages']);
    Route::post('/assign-loan-to-agent', [\App\Http\Controllers\Admin\LoanApplicationController::class, 'assignLoanToAgent']);
    Route::post('/un-assign-loan-to-agent', [\App\Http\Controllers\Admin\LoanApplicationController::class, 'unAssignLoanToAgent']);
    Route::get('/get-assigned-loans/{userId}', [\App\Http\Controllers\Admin\LoanApplicationController::class, 'getAssignedLoans']);
    Route::get('/get-follow-up-records/{loanId}', [\App\Http\Controllers\Admin\LoanApplicationController::class, 'getFollowUpRecords']);
    Route::get('/get-call-logs/{userId}', [\App\Http\Controllers\Agent\LoanApplicationController::class, 'getCustomerCallLogs']);

});
