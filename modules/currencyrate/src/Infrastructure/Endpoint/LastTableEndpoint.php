<?php

declare(strict_types=1);

namespace CurrencyRate\Infrastructure\Endpoint;

final class LastTableEndpoint implements NbpEndpointInterface
{
    public function __construct(private string $tableType = 'A')
    {
    }

    public function buildPath(): string
    {
        return sprintf('/api/exchangerates/tables/%s/last/1/?format=json', strtoupper($this->tableType));
    }
}
