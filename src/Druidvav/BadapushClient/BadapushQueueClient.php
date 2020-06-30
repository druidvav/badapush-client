<?php
namespace Druidvav\BadapushClient;

class BadapushQueueClient extends BadapushClient
{
    protected $method = 'payload.enqueue';
}
