<?php

namespace CCM\Leads;

use Illuminate\Support\ServiceProvider as LumenServiceProvider;

class ServiceProvider extends LumenServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {

    }

    /**
     * Register the application services.
     */
    public function register()
    {
        if (file_exists($helpers = __DIR__.'/Helpers/Helpers.php'))
        {
            require_once $helpers;
        }

        // Automatically apply the package configuration
//        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'leads');
        $this->mergeConfigFrom(__DIR__.'/../config/services.php', 'services');

        // Register the main class to use with the facade
        $this->app->singleton('leads', function () {
            return new Leads;
        });
    }
}
