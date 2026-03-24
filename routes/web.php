<?php

use App\Http\Controllers\Payments\HitPayRedirectController;
use App\Http\Controllers\Payments\HitPayWebhookController;
use Illuminate\Support\Facades\Route;

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

Route::get('/payments/redirect', HitPayRedirectController::class)->name('payments.hitpay.redirect');
Route::post('/payments/hitpay/webhook', HitPayWebhookController::class)->name('payments.hitpay.webhook');

include 'upgrade.php';
