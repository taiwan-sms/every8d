<?php

namespace TaiwanSms\Every8d\Tests;

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use TaiwanSms\Every8d\Every8dChannel;
use TaiwanSms\Every8d\Every8dMessage;

class Every8dChannelTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testSend()
    {
        $channel = new Every8dChannel(
            $client = m::mock('TaiwanSms\Every8d\Client')
        );

        $client->expects('send')->with([
            'subject' => null,
            'to' => $to = '+1234567890',
            'text' => $message = 'foo',
            'sendTime' => null,
        ]);

        $notifiable = new TestNotifiable(function () use ($to) {
            return $to;
        });
        $notification = new TestNotification(function () use ($message) {
            return $message;
        });

        $channel->send($notifiable, $notification);
    }

    public function testSendMessage()
    {
        $channel = new Every8dChannel(
            $client = m::mock('TaiwanSms\Every8d\Client')
        );

        $client->expects('send')->with([
            'subject' => $subject = 'bar',
            'to' => $to = '+1234567890',
            'text' => $message = 'foo',
            'sendTime' => $sendTime = date('YmdHis'),
        ]);

        $notifiable = new TestNotifiable(function () use ($to) {
            return $to;
        });
        $notification = new TestNotification(function () use ($subject, $message, $sendTime) {
            return Every8dMessage::create($message)->subject($subject)->sendTime($sendTime);
        });

        $channel->send($notifiable, $notification);
    }

    public function testSendFail()
    {
        $channel = new Every8dChannel(
            $client = m::mock('TaiwanSms\Every8d\Client')
        );

        $notifiable = new TestNotifiable(function () {
            return false;
        });

        $notification = new TestNotification(function () {
            return false;
        });

        self::assertNull($channel->send($notifiable, $notification));
    }
}

class TestNotifiable
{
    use Notifiable;

    private $resolver;

    public function __construct($resolver)
    {
        $this->resolver = $resolver;
    }

    public function routeNotificationForEvery8d()
    {
        $resolver = $this->resolver;

        return $resolver();
    }
}

class TestNotification extends Notification
{
    private $resolver;

    public function __construct($resolver)
    {
        $this->resolver = $resolver;
    }

    public function toEvery8d($notifiable)
    {
        $resolver = $this->resolver;

        return $resolver();
    }
}
