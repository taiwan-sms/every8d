<?php

namespace TaiwanSms\Every8d\Tests;

use Carbon\Carbon;
use DomainException;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as m;
use PHPUnit\Framework\TestCase;
use TaiwanSms\Every8d\Client;

class ClientTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testCredit()
    {
        $client = new Client(
            $userId = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $messageFactory->expects('createRequest')->with(
            'POST',
            'https://api.e8d.tw/API21/HTTP/getCredit.ashx',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query([
                'UID' => $userId,
                'PWD' => $password,
            ])
        )->andReturns(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->expects('sendRequest')->with($request)->andReturns(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->expects('getBody->getContents')->andReturns(
            $content = '300'
        );

        $this->assertSame((float) $content, $client->credit());
    }

    public function testCreditFail()
    {
        $this->expectExceptionCode(500);
        $this->expectException(DomainException::class);

        $client = new Client(
            $userId = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $messageFactory->expects('createRequest')->with(
            'POST',
            'https://api.e8d.tw/API21/HTTP/getCredit.ashx',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query([
                'UID' => $userId,
                'PWD' => $password,
            ])
        )->andReturns(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->expects('sendRequest')->with($request)->andReturns(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->expects('getBody->getContents')->andReturns('-');

        $client->credit();
    }

    public function testSend()
    {
        $client = new Client(
            $userId = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $params = [
            'to' => 'foo',
            'text' => 'foo',
            'sendTime' => date('YmdHis'),
        ];

        $query = array_filter(array_merge([
            'UID' => $userId,
            'PWD' => $password,
        ], [
            'SB' => isset($params['subject']) ? $params['subject'] : null,
            'MSG' => $params['text'],
            'DEST' => $params['to'],
            'ST' => empty($params['sendTime']) === false ? Carbon::parse($params['sendTime'])->format('YmdHis') : null,
        ]));

        $messageFactory->expects('createRequest')->with(
            'POST',
            'https://oms.every8d.com/API21/HTTP/sendSMS.ashx',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturns(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->expects('sendRequest')->with($request)->andReturns(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->expects('getBody->getContents')->andReturns(
            $content = '285.0,1,1.0,0,d0ad6380-4842-46a5-a1eb-9888e78fefd8'
        );

        $this->assertSame([
            'credit' => 285.0,
            'sended' => 1,
            'cost' => 1.0,
            'unsend' => 0,
            'batchId' => 'd0ad6380-4842-46a5-a1eb-9888e78fefd8',
        ], $client->setSmsHost('oms.every8d.com')->send($params));
        $this->assertSame((float) '285', $client->credit());
    }

    public function testSendFail()
    {
        $this->expectExceptionCode(500);
        $this->expectException(DomainException::class);
        $client = new Client(
            $userId = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );
        $params = [
            'to' => 'foo',
            'text' => 'foo',
        ];

        $query = array_filter(array_merge([
            'UID' => $userId,
            'PWD' => $password,
        ], [
            'SB' => isset($params['subject']) ? $params['subject'] : null,
            'MSG' => $params['text'],
            'DEST' => $params['to'],
            'ST' => isset($params['ST']) ? Carbon::parse($params['ST'])->format('YmdHis') : null,
        ]));

        $messageFactory->expects('createRequest')->with(
            'POST',
            'https://api.e8d.tw/API21/HTTP/sendSMS.ashx',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturns(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->expects('sendRequest')->with($request)->andReturns(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->expects('getBody->getContents')->andReturns(
            $content = '-'
        );

        $client->send($params);
    }

    public function testSendMMS()
    {
        $client = new Client(
            $userId = 'foo',
            $password = 'foo',
            $httpClient = m::mock('Http\Client\HttpClient'),
            $messageFactory = m::mock('Http\Message\MessageFactory')
        );

        $params = [
            'to' => 'foo',
            'text' => 'foo',
            'sendTime' => date('YmdHis'),
            'attachment' => 'attachment',
            'type' => 'jpg',
        ];

        $query = array_filter(array_merge([
            'UID' => $userId,
            'PWD' => $password,
        ], [
            'SB' => isset($params['subject']) ? $params['subject'] : null,
            'MSG' => $params['text'],
            'DEST' => $params['to'],
            'ST' => empty($params['sendTime']) === false ? Carbon::parse($params['sendTime'])->format('YmdHis') : null,
            'ATTACHMENT' => $params['attachment'],
            'TYPE' => $params['type'],
        ]));

        $messageFactory->expects('createRequest')->with(
            'POST',
            'https://api.e8d.tw/API21/HTTP/snedMMS.ashx',
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($query)
        )->andReturns(
            $request = m::mock('Psr\Http\Message\RequestInterface')
        );

        $httpClient->expects('sendRequest')->with($request)->andReturns(
            $response = m::mock('Psr\Http\Message\ResponseInterface')
        );

        $response->expects('getBody->getContents')->andReturns(
            '285.0,1,1.0,0,d0ad6380-4842-46a5-a1eb-9888e78fefd8'
        );

        $this->assertSame([
            'credit' => 285.0,
            'sended' => 1,
            'cost' => 1.0,
            'unsend' => 0,
            'batchId' => 'd0ad6380-4842-46a5-a1eb-9888e78fefd8',
        ], $client->sendMMS($params));
        $this->assertSame((float) '285', $client->credit());
    }
}
