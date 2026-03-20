<?php

declare(strict_types=1);

namespace CurrencyRate\Infrastructure\Endpoint;

final class TableRangeEndpoint
{
    public function __construct(private string $tableType = 'A')
    {
    }

    public function buildPath(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): string
    {
        return sprintf(
            '/api/exchangerates/tables/%s/%s/%s/?format=json',
            strtoupper($this->tableType),
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );
    }
}
