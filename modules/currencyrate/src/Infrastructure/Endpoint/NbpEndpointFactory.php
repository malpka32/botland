<?php

declare(strict_types=1);

namespace CurrencyRate\Infrastructure\Endpoint;

use CurrencyRate\Application\Support\CurrencyIsoCode;

final class NbpEndpointFactory
{
    public function createLastTableEndpoint(string $tableType): LastTableEndpoint
    {
        return new LastTableEndpoint($this->normalizeTableType($tableType));
    }

    public function createTableRangeEndpoint(string $tableType): TableRangeEndpoint
    {
        return new TableRangeEndpoint($this->normalizeTableType($tableType));
    }

    public function createLatestCurrencyRateEndpoint(string $tableType): LatestCurrencyRateEndpoint
    {
        return new LatestCurrencyRateEndpoint($this->normalizeTableType($tableType));
    }

    private function normalizeTableType(string $tableType): string
    {
        $normalizedType = CurrencyIsoCode::normalize($tableType);
        if ($normalizedType !== 'A' && $normalizedType !== 'B') {
            throw new \InvalidArgumentException(sprintf('Unsupported NBP table type "%s".', $tableType));
        }

        return $normalizedType;
    }
}
