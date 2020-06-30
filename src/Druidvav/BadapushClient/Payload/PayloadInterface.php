<?php
namespace Druidvav\BadapushClient\Payload;

interface PayloadInterface
{
    public function isDevelopment(): bool;
    public function getPayload();
    public function getDeviceId(): string;
    public function getExternalId(): ?string;
}
