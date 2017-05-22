<?php
namespace RobinFranssen\AnalyzeLocale\Providers;

use RobinFranssen\AnalyzeLocale\Console\Commands\AllKeysCommand;
use RobinFranssen\AnalyzeLocale\Console\Commands\AnalyzeLocaleCommand;
use RobinFranssen\AnalyzeLocale\Console\Commands\InvalidCommand;
use RobinFranssen\AnalyzeLocale\Console\Commands\UntranslatedCommand;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app->singleton('command.locale.scan', function () {
            return new AnalyzeLocaleCommand();
        });

        $this->app->singleton('command.locale.allkeys', function () {
            return new AllKeysCommand();
        });

        $this->app->singleton('command.locale.untranslated', function () {
            return new UntranslatedCommand();
        });

        $this->app->singleton('command.locale.invalid', function () {
            return new InvalidCommand();
        });

        $this->commands([
            'command.locale.scan',
            'command.locale.allkeys',
            'command.locale.untranslated',
            'command.locale.invalid',
        ]);
    }
}