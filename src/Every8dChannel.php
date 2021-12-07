<?php

namespace TaiwanSms\Every8d;

use Illuminate\Notifications\Notification;
use Psr\Http\Client\ClientExceptionInterface;

class Every8dChannel
{
    /**
     * $client.
     *
     * @var Client
     */
    private $client;

    /**
     * __construct.
     *
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Send the given notification.
     *
     * @param mixed $notifiable
     * @param Notification $notification
     * @return array|void
     * @throws ClientExceptionInterface
     */
    public function send($notifiable, Notification $notification)
    {
        if (! $to = $notifiable->routeNotificationFor('every8d')) {
            return;
        }

        $message = $notification->toEvery8d($notifiable);

        if (is_string($message)) {
            $message = new Every8dMessage($message);
        }

        return $this->client->send([
            'subject' => $message->subject,
            'to' => $to,
            'text' => trim($message->content),
            'sendTime' => $message->sendTime,
        ]);
    }
}
