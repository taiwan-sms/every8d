<?php

namespace TaiwanSms\Every8d\Tests;

use Illuminate\Config\Repository;
use Illuminate\Container\Container;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;
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
        $config->set('services.every8d', ['user_id' => 'foo', 'password' => 'bar', 'sms_host' => 'api.foo.bar']);
        $app = m::mock(new Container());
        $app->instance('config', $config);
        $serviceProvider = new Every8dServiceProvider($app);

        $app->expects('singleton')->with('TaiwanSms\Every8d\Client', m::on(function ($closure) use ($app) {
            $client = $closure($app);
            $this->assertPropertyEquals('foo', $client, 'userId');
            $this->assertPropertyEquals('bar', $client, 'password');
            $this->assertPropertyEquals('api.foo.bar', $client, 'smsHost');

            return $client instanceof Client;
        }));

        $serviceProvider->register();
    }

    /**
     * @param string $expected
     * @param mixed $object
     * @param $propertyName
     * @return void
     * @throws ReflectionException
     */
    private function assertPropertyEquals($expected, $object, $propertyName)
    {
        $reflector = new ReflectionClass(get_class($object));
        $reflectionProperty = $reflector->getProperty($propertyName);
        $reflectionProperty->setAccessible(true);
        self::assertEquals($expected, $reflectionProperty->getValue($object));
    }
}
