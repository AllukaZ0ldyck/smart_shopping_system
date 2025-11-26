<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HitpayController;
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

Route::get('/', function () {
    return view('welcome');
});

Route::get('/hitpay/callback', [HitpayController::class, 'callback'])
    ->name('hitpay.callback');

Route::post('/hitpay/webhook', [HitpayController::class, 'webhook'])
    ->name('hitpay.webhook');
include 'upgrade.php';

Route::get('/payment/success', function (Request $request) {
    return redirect('/pos?payment=success&ref='.$request->reference);
})->name('payment.success');

Route::get('/payment/failed', function (Request $request) {
    return redirect('/pos?payment=failed&ref='.$request->reference);
})->name('payment.failed');

Route::get('/pos', function () {
    return view('payment.pos'); // or the correct file
});
