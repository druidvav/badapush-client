<?php
namespace Druidvav\BadapushClient;

use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\RequestException;
use Druidvav\BadapushClient\Entity\Message;
use Druidvav\BadapushClient\Payload\PayloadInterface;
use Druidvav\BadapushClient\Exception\ClientException;
use Druidvav\BadapushClient\Exception\InternalErrorException;
use Druidvav\BadapushClient\Exception\InvalidPayloadException;
use Druidvav\BadapushClient\Exception\InvalidSubscribeIdException;

class BadapushClient
{
    protected $apiUrl;
    protected $method = 'payload.send';
    protected $apiKey;
    protected $httpClient;

    public function __construct($apiKey, $apiUrl = null, ClientInterface $httpClient = null)
    {
        $this->apiKey = $apiKey;
        $this->apiUrl = $apiUrl ?: 'https://badapush.ru/api/v2/jsonrpc';
        $this->httpClient = $httpClient ?: new Client();
    }

    /**
     * @param PayloadInterface $payload
     * @return string
     * @throws ClientException
     * @throws InternalErrorException
     * @throws InvalidPayloadException
     * @throws InvalidSubscribeIdException
     */
    public function sendPayload(PayloadInterface $payload): string
    {
        $result = $this->request([
            'id' => 1,
            'method' => $this->method,
            'params' => [
                'device_id' => $payload->getDeviceId(),
                'payload' => $payload->getPayload(),
                'is_development' => $payload->isDevelopment(),
                'external_id' => $payload->getExternalId()
            ]
        ]);
        return !empty($result['response']) ? $result['response'] : 'ok';
    }

    /**
     * @param int $fromId
     * @return array|Message[]
     * @throws ClientException
     * @throws InternalErrorException
     */
    public function retrieveMessages($fromId = 0): array
    {
        $result = $this->request([
            'id' => 1,
            'method' => 'payload.getMessages',
            'params' => [ 'from' => $fromId ]
        ]);
        $response = [ ];
        foreach ($result['list'] as $row) {
            $response[] = new Message($row);
        }
        return $response;
    }

    /**
     * @param $externalId
     * @param string $reason
     * @throws ClientException
     * @throws InternalErrorException
     */
    public function cancelCallsByExternalId($externalId, $reason = '')
    {
        $this->request([
            'id' => 1,
            'method' => 'caller.cancel',
            'params' => [ 'external_id' => $externalId, 'reason' => $reason ]
        ]);
    }

    protected function request($query)
    {
        try {
            $response = $this->httpClient->post($this->apiUrl, [
                'json' => $query,
                'headers' => [
                    'X-Authorization-Token' => $this->apiKey,
                ],
                'timeout' => 15,
                'connect_timeout' => 5,
                'http_errors' => false,
            ]);
        } catch (RequestException $e) {
            throw new InternalErrorException($e->getMessage(), 0, $e);
        }

        return $this->parseResponse($response);
    }

    protected function parseResponse($response)
    {
        $httpcode = $response->getStatusCode();
        $responseData = (string) $response->getBody();
        $data = json_decode($responseData, true);

        if (!empty($data['result']['result'])) {
            if ($data['result']['result'] == 'ok') {
                return $data['result'];
            } elseif ($data['result']['result'] == 'error') {
                switch ($data['result']['error_code']) {
                    default: throw new ClientException($data['result']['error_message']);
                    case InvalidSubscribeIdException::TYPE: throw new InvalidSubscribeIdException($data['result']['error_message']);
                    case InvalidPayloadException::TYPE: throw new InvalidPayloadException($data['result']['error_message']);
                    case InternalErrorException::TYPE: throw new InternalErrorException($data['result']['error_message']);
                }
            }
        } elseif (!empty($data['error'])) {
            throw new ClientException($data['error']['code'] . ': ' . $data['error']['message']);
        } elseif ($httpcode == 502) {
            throw new InternalErrorException('Service is temporary shut down');
        } elseif ($httpcode == 500 || $httpcode == 504) {
            throw new InternalErrorException('Service is temporary down');
        }
        throw new ClientException($httpcode . ': ' . $responseData);
    }
}
