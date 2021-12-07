<?php

namespace TaiwanSms\Every8d;

use Carbon\Carbon;
use DomainException;
use Http\Client\HttpClient;
use Http\Discovery\HttpClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;
use Psr\Http\Client\ClientExceptionInterface;

class Client
{
    /**
     * @var string
     */
    private $smsHost = 'api.e8d.tw';

    /**
     * @var float|null
     */
    public $credit;

    /**
     * @var string
     */
    private $userId;

    /**
     * @var string
     */
    private $password;

    /**
     * @var HttpClient
     */
    private $httpClient;

    /**
     * @var MessageFactory
     */
    private $messageFactory;

    /**
     * __construct.
     *
     * @param string $userId
     * @param string $password
     * @param HttpClient $httpClient
     * @param MessageFactory $messageFactory
     */
    public function __construct($userId, $password, HttpClient $httpClient = null, MessageFactory $messageFactory = null)
    {
        $this->userId = $userId;
        $this->password = $password;
        $this->httpClient = $httpClient ?: HttpClientDiscovery::find();
        $this->messageFactory = $messageFactory ?: MessageFactoryDiscovery::find();
    }

    public function setSmsHost($smsHost)
    {
        $this->smsHost = $smsHost;

        return $this;
    }

    /**
     * send.
     *
     * @param array $params
     * @return array
     * @throws ClientExceptionInterface
     */
    public function send($params)
    {
        $response = $this->doRequest('sendSMS.ashx', array_filter(array_merge([
            'UID' => $this->userId,
            'PWD' => $this->password,
            'SB' => null,
            'MSG' => null,
            'DEST' => null,
            'ST' => null,
            'RETRYTIME' => null,
        ], $this->remapParams($params))));

        if ($this->isValidResponse($response) === false) {
            throw new DomainException($response, 500);
        }

        return $this->parseResponse($response);
    }

    /**
     * sendMMS.
     *
     * @param array $params
     * @return array
     * @throws ClientExceptionInterface
     */
    public function sendMMS($params)
    {
        $response = $this->doRequest('snedMMS.ashx', array_filter(array_merge([
            'UID' => $this->userId,
            'PWD' => $this->password,
            'SB' => null,
            'MSG' => null,
            'DEST' => null,
            'ST' => null,
            'RETRYTIME' => null,
            'ATTACHMENT' => null,
            'TYPE' => null,
        ], $this->remapParams($params))));

        if ($this->isValidResponse($response) === false) {
            throw new DomainException($response, 500);
        }

        return $this->parseResponse($response);
    }

    /**
     * credit.
     *
     * @return float
     * @throws ClientExceptionInterface
     */
    public function credit()
    {
        if (is_null($this->credit) === false) {
            return $this->credit;
        }

        $response = $this->doRequest('getCredit.ashx', [
            'UID' => $this->userId,
            'PWD' => $this->password,
        ]);

        if ($this->isValidResponse($response) === false) {
            throw new DomainException($response, 500);
        }

        return $this->setCredit($response)->credit;
    }

    /**
     * setCredit.
     *
     * @param string|float $credit
     */
    private function setCredit($credit)
    {
        $this->credit = (float) $credit;

        return $this;
    }

    /**
     * isValidResponse.
     *
     * @param string $response
     *
     * @return bool
     */
    private function isValidResponse($response)
    {
        return strpos($response, '-') !== 0;
    }

    /**
     * doRequest.
     *
     * @param string $uri
     * @param array $params
     * @return string
     * @throws ClientExceptionInterface
     */
    private function doRequest($uri, $params)
    {
        $request = $this->messageFactory->createRequest(
            'POST',
            $this->getUrl($uri),
            ['Content-Type' => 'application/x-www-form-urlencoded; charset=utf-8'],
            http_build_query($params)
        );
        $response = $this->httpClient->sendRequest($request);

        return $response->getBody()->getContents();
    }

    /**
     * remapParams.
     *
     * @param array $params
     * @return array
     */
    private function remapParams($params)
    {
        if (empty($params['subject']) === false) {
            $params['SB'] = $params['subject'];
            unset($params['subject']);
        }

        if (empty($params['to']) === false) {
            $params['DEST'] = $params['to'];
            unset($params['to']);
        }

        if (empty($params['text']) === false) {
            $params['MSG'] = $params['text'];
            unset($params['text']);
        }

        if (empty($params['sendTime']) === false) {
            $params['ST'] = Carbon::parse($params['sendTime'])->format('YmdHis');
            unset($params['sendTime']);
        }

        if (empty($params['attachment']) === false) {
            $params['ATTACHMENT'] = $params['attachment'];
            unset($params['attachment']);
        }

        if (empty($params['type']) === false) {
            $params['TYPE'] = $params['type'];
            unset($params['type']);
        }

        return $params;
    }

    /**
     * parseResponse.
     *
     * @param string $body
     * @return array
     */
    private function parseResponse($body)
    {
        list($credit, $sended, $cost, $unsend, $batchId) = explode(',', $body);

        return [
            'credit' => $this->setCredit($credit)->credit,
            'sended' => (int) $sended,
            'cost' => (float) $cost,
            'unsend' => (int) $unsend,
            'batchId' => $batchId,
        ];
    }

    /**
     * @param $uri
     * @return string
     */
    private function getUrl($uri)
    {
        return rtrim('https://'.$this->smsHost.'/API21/HTTP').'/'.$uri;
    }
}
