<?php

namespace Royalcms\Laravel\JsonRpcClient;

use Illuminate\Support\ServiceProvider;


class JsonRpcClientServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/rpc-services.php' => config_path('rpc-services.php'),
        ], 'config');

    }

}
