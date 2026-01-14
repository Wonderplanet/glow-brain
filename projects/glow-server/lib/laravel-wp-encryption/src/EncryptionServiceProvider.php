<?php
namespace WonderPlanet;

use Illuminate\Support\ServiceProvider;

class EncryptionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/wp_encryption.php' => config_path('wp_encryption.php'),
        ], 'wp');
    }

    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/wp_encryption.php', 'wp_encryption'
        );
    }
}