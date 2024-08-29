<?php

namespace Ahmedessam\LaravelAutotranslate;

use Illuminate\Support\ServiceProvider;
use Ahmedessam\LaravelAutotranslate\Console\Commands\SyncTranslationsCommand;

class LaravelAutotranslateServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind('auto-translate', function () {
            return new Translation();
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                SyncTranslationsCommand::class,
            ]);
        }

        $this->publishes([
            __DIR__.'/Console/Commands/SyncTranslationsCommand.php' => app_path('Console/Commands/SyncTranslationsCommand.php'),
        ], 'command');

        $this->publishes([
            __DIR__ . '/stubs/translation.stub' => base_path('stubs/translation.stub'),
        ], 'stubs');

        $this->publishes([
            __DIR__ . '/config/auto-translate.php' => config_path('auto-translate.php'),
        ], 'config');
    }
}
