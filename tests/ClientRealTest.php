<?php

namespace TaiwanSms\Every8d\Tests;

use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;
use TaiwanSms\Every8d\Client;

class ClientRealTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    protected $userId = '';
    protected $password = '';
    protected $options = [
        'to' => '',
        'text' => '中文測試',
    ];

    protected function isUnderTest()
    {
        if (empty($this->userId) === true || empty($this->password) === true) {
            $this->markTestSkipped('Please set uid and password');
        }
    }

    public function testCredit()
    {
        $this->isUnderTest();

        $client = new Client($this->userId, $this->password);

        $this->assertIsFloat($client->credit());
    }

    public function testSend()
    {
        $this->isUnderTest();

        $client = new Client($this->userId, $this->password);

        $this->assertIsArray($client->send([
            'to' => $this->options['to'],
            'text' => $this->options['text'],
        ]));
    }
}
