<?php
namespace Druidvav\BadapushClient\Payload;

interface PayloadInterface
{
    public function isDevelopment(): bool;
    /** @return mixed */
    public function getPayload();
    public function getDeviceId(): string;
    public function getExternalId(): ?string;
}
