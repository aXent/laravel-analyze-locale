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
        $this->app['command.locale.scan'] = $this->app->share(
            function () {
                return new AnalyzeLocaleCommand();
            }
        );

        $this->app['command.locale.allkeys'] = $this->app->share(
            function () {
                return new AllKeysCommand();
            }
        );

        $this->app['command.locale.untranslated'] = $this->app->share(
            function () {
                return new UntranslatedCommand();
            }
        );

        $this->app['command.locale.invalid'] = $this->app->share(
            function () {
                return new InvalidCommand();
            }
        );

        $this->commands([
            'command.locale.scan',
            'command.locale.allkeys',
            'command.locale.untranslated',
            'command.locale.invalid',
        ]);
    }
}