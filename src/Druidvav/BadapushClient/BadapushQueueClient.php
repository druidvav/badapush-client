<?php
namespace Druidvav\BadapushClient;

use Druidvav\BadapushClient\Payload\PayloadInterface;

class BadapushQueueClient extends BadapushClient
{
    protected $method = 'payload.enqueue';

    protected $batchMode = false;
    protected $batch = [ ];

    public function beginBatch(): void
    {
        $this->batchMode = true;
    }

    public function sendPayload(PayloadInterface $payload): string
    {
        if ($this->batchMode) {
            $this->batch[] = $payload;
            return 'ok';
        } else {
            return parent::sendPayload($payload);
        }
    }

    public function commitBatch(): void
    {
        $batch = [ ];
        foreach ($this->batch as $payload) {
            $batch[] = [
                'device_id' => $payload->getDeviceId(),
                'payload' => $payload->getPayload(),
                'is_development' => $payload->isDevelopment(),
                'external_id' => $payload->getExternalId()
            ];
        }
        $result = $this->request([
            'id' => 1,
            'method' => 'payload.enqueueBatch',
            'params' => [ $batch ]
        ]);
        $this->batchMode = false;
        $this->batch = [ ];
    }
}
