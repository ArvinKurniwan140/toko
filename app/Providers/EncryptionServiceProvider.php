<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\EncryptionService;
use App\Services\KeyManagementService;
use App\Services\HybridEncryptionService;

class EncryptionServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(KeyManagementService::class, function ($app) {
            return new KeyManagementService();
        });

        // Register Encryption Service sebagai singleton dan injeksi KMS
        $this->app->singleton(EncryptionService::class, function ($app) {
            return new EncryptionService(
                $app->make(KeyManagementService::class)
            );
        });

        // Register Hybrid Encryption Service (AES-256 + RSA)
        $this->app->singleton(HybridEncryptionService::class, function ($app) {
            return new HybridEncryptionService(
                $app->make(EncryptionService::class)
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
