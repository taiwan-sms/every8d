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
            $userId = Arr::get($config, 'user_id');
            $password = Arr::get($config, 'password');
            $smsHost = Arr::get($config, 'sms_host', 'api.e8d.tw');

            return (new Client($userId, $password))->setSmsHost($smsHost);
        });
    }
}
