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
Route::middleware(['auth:sanctum'])->group(function() {

    Route::post('/create-admin', [\App\Http\Controllers\Admin\AdminController::class, 'create']);
    Route::get('/get-admins', [\App\Http\Controllers\Admin\AdminController::class, 'getAll']);
    Route::get('/get-admin/{id}', [\App\Http\Controllers\Admin\AdminController::class, 'getAdmin']);

});



/*
|--------------------------------------------------------------------------
| Permissions management Routes
|--------------------------------------------------------------------------
|
*/
Route::middleware(['auth:sanctum'])->group(function() {

    Route::get('/get-permissions', [\App\Http\Controllers\Admin\RolePermissionController::class, 'getAll']);
    Route::get('/get-user-permissions/{userId}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'getUserPermissions']);
    Route::post('/assign-permission', [\App\Http\Controllers\Admin\RolePermissionController::class, 'assign']);

});


/*
|--------------------------------------------------------------------------
| Admin config Routes
|--------------------------------------------------------------------------
|
*/
Route::middleware(['auth:sanctum'])->group(function() {

    Route::post('/add-cusboarding-page', [\App\Http\Controllers\Admin\ConfigCusBoardingController::class, 'addPage']);
    Route::post('/add-cusboarding-field', [\App\Http\Controllers\Admin\ConfigCusBoardingController::class, 'addFieldToPage']);
    Route::delete('/delete-cusboarding-field/{id}', [\App\Http\Controllers\Admin\ConfigCusBoardingController::class, 'removeField']);
    Route::delete('/delete-cusboarding-page/{id}', [\App\Http\Controllers\Admin\ConfigCusBoardingController::class, 'removePage']);
    Route::get('/get-cusboarding-pages-fields', [\App\Http\Controllers\Admin\ConfigCusBoardingController::class, 'getPagesWithFields']);

});
