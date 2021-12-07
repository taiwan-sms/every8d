<?php

namespace TaiwanSms\Every8d;

use Illuminate\Support\Arr;
use Illuminate\Support\ServiceProvider;

class Every8dServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Client::class, function ($app) {
            $config = Arr::get($app['config'], 'services.every8d', []);

            return new Client(Arr::get($config, 'user_id'), Arr::get($config, 'password'));
        });
    }
}
