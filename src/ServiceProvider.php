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
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'leads');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'leads');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
//            $this->publishes([
//                __DIR__.'/../config/config.php' => config_path('leads.php'),
//            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/leads'),
            ], 'views');*/

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/leads'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/leads'),
            ], 'lang');*/

            // Registering package commands.
            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Load the helpers.php
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
