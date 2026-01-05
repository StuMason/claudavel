<?php

declare(strict_types=1);

namespace Stumason\Claudavel;

use Illuminate\Support\ServiceProvider;
use Stumason\Claudavel\Commands\GenerateTypesCommand;
use Stumason\Claudavel\Commands\InstallCommand;
use Stumason\Claudavel\Commands\MakeActionCommand;
use Stumason\Claudavel\Commands\MakeDtoCommand;

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
                MakeActionCommand::class,
                MakeDtoCommand::class,
                GenerateTypesCommand::class,
            ]);
        }
    }
}
