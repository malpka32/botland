<?php

declare(strict_types=1);

namespace CurrencyRate\Application\ExchangeRate;

use CurrencyRate\Domain\Collection\NbpTableCollection;
use CurrencyRate\Domain\Dto\Api\NbpCurrencySeries;
use CurrencyRate\Domain\Dto\Api\NbpTable;

interface ExchangeRateGatewayInterface
{
    public function fetchLatestTable(string $tableType): NbpTable;

    public function fetchLatestCurrencyRate(string $tableType, string $isoCode): NbpCurrencySeries;

    public function fetchTableRange(string $tableType, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): NbpTableCollection;

    public function fetchCurrencySeries(string $tableType, string $isoCode, int $topCount = 1): NbpCurrencySeries;
}
