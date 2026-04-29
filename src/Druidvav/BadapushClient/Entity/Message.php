<?php
namespace Druidvav\BadapushClient\Entity;

use Druidvav\BadapushClient\Exception\InvalidSubscribeIdException;

class Message
{
    protected $id;
    protected $deviceId;
    protected $payload;
    protected $externalId;

    protected $type;
    protected $response;
    protected $sentVia;
    protected $requestTime;

    public function __construct(array $array)
    {
        $this->id = $array['id'];
        $this->deviceId = $array['recipient_id'];
        $this->externalId = $array['external_id'];
        $this->payload = $array['payload'];
        $this->type = $array['error'];
        $this->response = $array['error_msg'];
        $this->sentVia = $array['sent_via'];
        $this->requestTime = new \DateTime($array['request_time']);
    }

    /**
     * @return integer
     */
    public function getId(): int
    {
        return $this->id;
    }

    public function getDeviceId(): string
    {
        return $this->deviceId;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /** @return mixed */
    public function getPayload()
    {
        return $this->payload;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function isMessage(): bool
    {
        return $this->type == 'message';
    }

    public function isInvalidId(): bool
    {
        return $this->type == InvalidSubscribeIdException::TYPE;
    }

    public function getResponse(): string|array|null
    {
        return $this->response;
    }

    public function getSentVia(): ?string
    {
        return $this->sentVia;
    }

    public function getRequestTime(): \DateTime
    {
        return $this->requestTime;
    }
}
