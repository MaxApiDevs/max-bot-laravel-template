<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

$maxWebhookToken = trim((string) env('MAX_BOT_TOKEN', 'bot-token-not-configured'), '/');
$maxWebhookPath = sprintf('max/%s/webhook', $maxWebhookToken);

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) use ($maxWebhookPath): void {
        $middleware->validateCsrfTokens(except: [
            $maxWebhookPath,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
