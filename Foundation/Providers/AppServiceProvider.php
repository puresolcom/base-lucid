<?php

namespace Awok\Foundation\Providers;

use Awok\Console\Commands\ControllerMakeCommand;
use Awok\Console\Commands\CrudMakeCommand;
use Awok\Console\Commands\FeatureMakeCommand;
use Awok\Console\Commands\JobMakeCommand;
use Awok\Console\Commands\ModelMakeCommand;
use Awok\Console\Commands\ValidatorMakeCommand;
use Awok\Foundation\Exceptions\Handler\JsonExceptionsHandler;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(
            ExceptionHandler::class,
            JsonExceptionsHandler::class
        );

        $this->commands(
            [
                ControllerMakeCommand::class,
                ModelMakeCommand::class,
                FeatureMakeCommand::class,
                JobMakeCommand::class,
                ValidatorMakeCommand::class,
                CrudMakeCommand::class,
            ]
        );
    }
}
