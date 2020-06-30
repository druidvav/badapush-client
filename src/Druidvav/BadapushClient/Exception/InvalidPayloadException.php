<?php
namespace Druidvav\BadapushClient\Exception;

class InvalidPayloadException extends ClientException
{
    const TYPE = 'invalid_payload';

    protected $type = self::TYPE;
}