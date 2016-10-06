<?php

namespace Gtk\Larasearch;

use Illuminate\Support\ServiceProvider;
use Gtk\Larasearch\Console\ClearCommand;
use Gtk\Larasearch\Console\ImportCommand;

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
                ClearCommand::class,
            ]);

            $this->publishes([
                __DIR__.'/../config/larasearch.php' => config_path('larasearch.php'),
            ]);
        }
    }
}
