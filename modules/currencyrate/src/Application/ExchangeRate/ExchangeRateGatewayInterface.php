<?php

declare(strict_types=1);

namespace CurrencyRate\Application\ExchangeRate;

use CurrencyRate\Domain\Collection\NbpTableCollection;
use CurrencyRate\Domain\Dto\NbpCurrencySeries;
use CurrencyRate\Domain\Dto\NbpTable;

interface ExchangeRateGatewayInterface
{
    public function fetchLatestTable(string $tableType): NbpTable;

    public function fetchLatestTableA(): NbpTable;
    public function fetchLatestTableB(): NbpTable;

    public function fetchTableRange(string $tableType, \DateTimeImmutable $startDate, \DateTimeImmutable $endDate): NbpTableCollection;
    public function fetchTableARange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): NbpTableCollection;
    public function fetchTableBRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): NbpTableCollection;

    public function fetchCurrencySeries(string $tableType, string $isoCode, int $topCount = 1): NbpCurrencySeries;
    public function fetchCurrencySeriesA(string $isoCode, int $topCount = 1): NbpCurrencySeries;
    public function fetchCurrencySeriesB(string $isoCode, int $topCount = 1): NbpCurrencySeries;
}
