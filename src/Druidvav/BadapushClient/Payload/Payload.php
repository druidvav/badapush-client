<?php
namespace Druidvav\BadapushClient\Payload;

class Payload implements PayloadInterface
{
    protected $deviceId;
    protected $payload;
    protected $isDevelopment;
    protected $externalId;

    /** @param mixed $payload */
    public static function create(string $deviceId = '', $payload = []): Payload
    {
        return new Payload($deviceId, $payload);
    }

    /** @param mixed $payload */
    public function __construct(string $deviceId = '', $payload = [], bool $isDevelopment = false)
    {
        $this->deviceId = $deviceId;
        $this->payload = $payload;
        $this->isDevelopment = $isDevelopment;
    }

    public function isDevelopment(): bool
    {
        return $this->isDevelopment;
    }

    public function setIsDevelopment(bool $isDevelopment): Payload
    {
        $this->isDevelopment = $isDevelopment;
        return $this;
    }

    /** @return mixed */
    public function getPayload()
    {
        return $this->payload;
    }

    /** @param mixed $data */
    public function setPayload($data): Payload
    {
        $this->payload = $data;
        return $this;
    }

    /** @param mixed $data */
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
