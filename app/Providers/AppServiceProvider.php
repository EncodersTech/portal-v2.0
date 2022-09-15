<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\Setting;
use App\Models\AuditEvent;
use App\Services\USAGService;
use App\Services\USAIGCService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        try {
            AuditEvent::$auditEnabled = Setting::getSetting(Setting::AUDIT_ENABLED);
            if (config('app.debug'))
                Redis::enableEvents();

            app()->singleton(USAGService::class, function() {
                return new USAGService(
                    config('services.usag.env') == 'dev',
                    config('services.usag.require_key') ? config('services.usag.webhook_key') : null
                );
            });

            app()->singleton(USAIGCService::class, function() {
                return new USAIGCService(config('services.usaigc.env') == 'dev');
            });
        } catch (\Throwable $e) {
            Log::warning('AppServiceProvider : ' . $e->getMessage(), [
                'Throwable' => $e
            ]);
        }
    }
}
