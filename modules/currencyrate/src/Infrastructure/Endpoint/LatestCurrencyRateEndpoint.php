<?php

declare(strict_types=1);

namespace CurrencyRate\Infrastructure\Endpoint;

final class LatestCurrencyRateEndpoint
{
    public function __construct(private string $tableType = 'A')
    {
    }

    public function buildPath(string $isoCode): string
    {
        return sprintf(
            '/api/exchangerates/rates/%s/%s/?format=json',
            strtoupper($this->tableType),
            strtolower($isoCode)
        );
    }
}
