<?php
namespace RobinFranssen\AnalyzeLocale\Providers;

use RobinFranssen\AnalyzeLocale\Console\Commands\AllKeys;
use RobinFranssen\AnalyzeLocale\Console\Commands\AnalyzeLocale;
use RobinFranssen\AnalyzeLocale\Console\Commands\Invalid;
use RobinFranssen\AnalyzeLocale\Console\Commands\Untranslated;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    public function register()
    {
        $this->app['command.locale.scan'] = $this->app->share(
            function () {
                return new AnalyzeLocale;
            }
        );

        $this->app['command.locale.allkeys'] = $this->app->share(
            function () {
                return new AllKeys();
            }
        );

        $this->app['command.locale.untranslated'] = $this->app->share(
            function () {
                return new Untranslated();
            }
        );

        $this->app['command.locale.invalid'] = $this->app->share(
            function () {
                return new Invalid();
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