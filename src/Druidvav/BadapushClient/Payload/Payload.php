<?php
namespace Druidvav\BadapushClient\Payload;

class Payload implements PayloadInterface
{
    protected $deviceId;
    protected $payload;
    protected $isDevelopment;
    protected $externalId;

    public static function create($deviceId = '', $payload = [ ]): Payload
    {
        return new Payload($deviceId, $payload);
    }

    public function __construct($deviceId = '', $payload = [ ], $isDevelopment = false)
    {
        $this->deviceId = $deviceId;
        $this->payload = $payload;
        $this->isDevelopment = $isDevelopment;
    }

    public function isDevelopment(): bool
    {
        return $this->isDevelopment;
    }

    public function setIsDevelopment($isDevelopment): Payload
    {
        $this->isDevelopment = $isDevelopment;
        return $this;
    }

    public function getPayload()
    {
        return $this->payload;
    }

    public function setPayload($data): Payload
    {
        $this->payload = $data;
        return $this;
    }

    public function setPayloadAps($data): Payload
    {
        $this->payload['aps'] = $data;
        return $this;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function setDeviceId(string $deviceId): Payload
    {
        $this->deviceId = $deviceId;
        return $this;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(?string $externalId): Payload
    {
        $this->externalId = $externalId;
        return $this;
    }
}
