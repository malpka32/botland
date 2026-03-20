<?php

declare(strict_types=1);

namespace CurrencyRate\Infrastructure\Endpoint;

interface NbpEndpointInterface
{
    public function buildPath(): string;
}
