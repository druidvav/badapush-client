<?php
namespace Druidvav\BadapushClient\Exception;

class InternalErrorException extends ClientException
{
    const TYPE = 'internal_error';

    protected $type = self::TYPE;
}