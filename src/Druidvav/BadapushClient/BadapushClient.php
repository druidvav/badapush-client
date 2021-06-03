<?php
namespace Druidvav\BadapushClient;

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

    public function __construct($apiKey, $apiUrl = null)
    {
        $this->apiKey = $apiKey;
        if (!$apiUrl) {
            $this->apiUrl = 'https://badapush.ru/api/v2/jsonrpc';
        } else {
            $this->apiUrl = $apiUrl;
        }
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
        $ch = curl_init($this->apiUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($query));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Authorization-Token: ' . $this->apiKey,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $responseData = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $errno = curl_errno($ch);
        $error = curl_error($ch);
        curl_close($ch);

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
        } elseif ($errno == 28) {
            throw new InternalErrorException('TIMEOUT ' . $error);
        }
        throw new ClientException($httpcode . '/' . $errno . ': ' . ($error ?: $responseData));
    }
}
