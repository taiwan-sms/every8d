<?php

namespace TaiwanSms\Every8d\Tests;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use TaiwanSms\Every8d\Client;
use TaiwanSms\Every8d\Every8dServiceProvider;

class Every8dServiceProviderTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testRegister()
    {
        if (PHP_VERSION_ID < 50600 === true) {
            $this->markTestSkipped('PHP VERSION must bigger then 5.6');
        }

        $config = new Repository();
        $config->set('services.every8d', ['user_id' => 'foo', 'password' => 'bar']);
        $app = m::mock(new Container());
        $app->instance('config', $config);
        $serviceProvider = new Every8dServiceProvider($app);

        $app->expects('singleton')->with('TaiwanSms\Every8d\Client', m::on(function ($closure) use ($app) {
            return $closure($app) instanceof Client;
        }));

        $serviceProvider->register();
    }
}
