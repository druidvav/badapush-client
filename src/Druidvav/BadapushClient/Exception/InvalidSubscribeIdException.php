<?php
namespace Druidvav\BadapushClient\Exception;

class InvalidSubscribeIdException extends ClientException
{
    const TYPE = 'invalid_id';

    protected $type = self::TYPE;
}