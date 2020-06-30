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

    public function __construct($array)
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getDeviceId()
    {
        return $this->deviceId;
    }

    /**
     * @return string
     */
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @return array
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isMessage()
    {
        return $this->type == 'message';
    }

    /**
     * @return bool
     */
    public function isInvalidId()
    {
        return $this->type == InvalidSubscribeIdException::TYPE;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @return string
     */
    public function getSentVia()
    {
        return $this->sentVia;
    }

    /**
     * @return \DateTime
     */
    public function getRequestTime()
    {
        return $this->requestTime;
    }
}
