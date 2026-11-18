<?php

namespace App\Providers;

use App\Contracts\SmsChannelInterface;
use App\Contracts\WhatsAppChannelInterface;
use App\Services\InstitutionContext;
use App\Services\Notifications\Channels\LogSmsChannel;
use App\Services\Notifications\Channels\LogWhatsAppChannel;
use App\Services\SettingsService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(InstitutionContext::class);
        $this->app->singleton(SettingsService::class);
        $this->app->bind(SmsChannelInterface::class, LogSmsChannel::class);
        $this->app->bind(WhatsAppChannelInterface::class, LogWhatsAppChannel::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
