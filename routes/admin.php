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

Route::middleware(['auth:sanctum'])->post('/create-admin', [\App\Http\Controllers\Admin\AdminController::class, 'create']);
Route::middleware(['auth:sanctum'])->get('/get-admins', [\App\Http\Controllers\Admin\AdminController::class, 'getAll']);


/*
|--------------------------------------------------------------------------
| Permissions management Routes
|--------------------------------------------------------------------------
|
*/

Route::middleware(['auth:sanctum'])->get('/get-permissions', [\App\Http\Controllers\Admin\RolePermissionController::class, 'getAll']);
Route::middleware(['auth:sanctum'])->get('/get-user-permissions/{userId}', [\App\Http\Controllers\Admin\RolePermissionController::class, 'getUserPermissions']);
Route::middleware(['auth:sanctum'])->post('/assign-permission', [\App\Http\Controllers\Admin\RolePermissionController::class, 'assign']);
