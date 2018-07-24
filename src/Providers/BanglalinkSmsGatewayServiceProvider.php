<?php

namespace Shipu\BanglalinkSmsGateway\Providers;

use Illuminate\Container\Container;
use Illuminate\Support\ServiceProvider;
use Shipu\BanglalinkSmsGateway\Services\Banglalink;
use Laravel\Lumen\Application as LumenApplication;
use Shipu\BanglalinkSmsGateway\Facades\Banglalink as BanglalinkFacade;
use Illuminate\Foundation\Application as LaravelApplication;

class BanglalinkSmsGatewayServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBanglalink();
    }

    /**
     * Setup the config.
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/../../config/banglalink-sms-gateway.php');
        // Check if the application is a Laravel OR Lumen instance to properly merge the configuration file.
        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('banglalink-sms-gateway.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('banglalink-sms-gateway');
        }
        $this->mergeConfigFrom($source, 'banglalink-sms-gateway');
    }

    /**
     * Register Banglalink class.
     */
    protected function registerBanglalink()
    {
        $this->app->bind('banglalink', function (Container $app) {
            return new Banglalink($app['config']->get('banglalink-sms-gateway'));
        });
        $this->app->alias('banglalink', BanglalinkFacade::class);
    }
}
