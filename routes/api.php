<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/
Route::get('/test', function () {
    \Illuminate\Support\Facades\Log::info('test-url called');
    return 'Application is running';
});

Route::match(['GET', 'POST'], '/payment-callback', [\App\Http\Controllers\PaymentController::class, 'paymentCallback']);


//Route::middleware('basic.auth')->get('push-event', function() {
//    \Illuminate\Support\Facades\Log::info('push-event called');
//    $loan = \App\Models\LoanApplication::with([])->first();
//    event(new \App\Events\LoanApplicationAssignedToAgent(loanApplication: $loan));
//    return "Event has been sent!";
//});
