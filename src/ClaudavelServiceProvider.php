<?php

namespace Stumason\Claudavel;

use Illuminate\Support\ServiceProvider;
use Stumason\Claudavel\Commands\InstallCommand;

class ClaudavelServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                InstallCommand::class,
            ]);
        }
    }
}
