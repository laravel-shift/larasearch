<?php

namespace Gtk\Larasearch;

use Gtk\Larasearch\Console\ImportCommand;
use Illuminate\Support\ServiceProvider;

class LarasearchServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(EngineManager::class, function ($app) {
            return new EngineManager($app);
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                ImportCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/scout.php' => config_path('scout.php'),
            ]);
        }
    }
}
