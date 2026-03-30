<?php

namespace App\Providers;

use App\Modules\Max\Contracts\MaxApiClientInterface;
use App\Modules\Max\Contracts\WebhookEventStoreInterface;
use App\Modules\Max\Http\MaxApiClient;
use App\Modules\Max\Storage\FileWebhookEventStore;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(MaxApiClientInterface::class, MaxApiClient::class);
        $this->app->bind(WebhookEventStoreInterface::class, FileWebhookEventStore::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
