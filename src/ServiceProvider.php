<?php

namespace CCM\Leads;

use Illuminate\Support\ServiceProvider as LumenServiceProvider;
use CCM\Leads\Providers\ValidatorServiceProvider;
use Route;

class ServiceProvider extends LumenServiceProvider
{
    private $path = __DIR__.'/../';
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadTranslationsFrom($this->path.'resources/lang', 'main');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        require_once $this->path.'src/Helpers/Helpers.php';

        $this->mergeConfigFrom($this->path.'config/bsgpsg.php', 'bsgpsg');
        $this->mergeConfigFrom($this->path.'config/esg.php', 'esg');
        $this->mergeConfigFrom($this->path.'config/main.php', 'main');
        $this->mergeConfigFrom($this->path.'config/services.php', 'services');
        $this->mergeConfigFrom($this->path.'config/signup.php', 'signup');

        // Register the main class to use with the facade
        $this->app->singleton('leads', function () {
            return new Leads;
        });

        $this->app->register(ValidatorServiceProvider::class);
    }
}
