<?php

use App\Http\Controllers\MaxDebugController;
use App\Http\Controllers\MaxWebhookController;
use App\Http\Middleware\EnsureMaxDebugEnabled;
use Illuminate\Support\Facades\Route;

$maxWebhookToken = trim((string) env('MAX_BOT_TOKEN', 'bot-token-not-configured'), '/');
$maxWebhookPath = sprintf('max/%s/webhook', $maxWebhookToken);

Route::get('/', function () {
    return view('welcome');
});

Route::post($maxWebhookPath, [MaxWebhookController::class, 'postWebhook'])
    ->name('max.webhook');

Route::middleware(EnsureMaxDebugEnabled::class)
    ->prefix('debug/max')
    ->name('max.debug.')
    ->group(function (): void {
        Route::get('/', [MaxDebugController::class, 'index'])->name('index');
        Route::post('/subscriptions/connect', [MaxDebugController::class, 'subscribe'])->name('subscriptions.connect');
        Route::post('/subscriptions/disconnect', [MaxDebugController::class, 'unsubscribe'])->name('subscriptions.disconnect');
        Route::get('/events/{event}', [MaxDebugController::class, 'showEvent'])->name('events.show');
    });
