<?php

declare(strict_types=1);

namespace CurrencyRate\Infrastructure\Endpoint;

final class CurrencySeriesEndpoint
{
    public function __construct(private string $tableType = 'A')
    {
    }

    public function buildPath(string $isoCode, int $topCount): string
    {
        return sprintf(
            '/api/exchangerates/rates/%s/%s/last/%d/?format=json',
            strtoupper($this->tableType),
            strtolower($isoCode),
            $topCount
        );
    }
}
