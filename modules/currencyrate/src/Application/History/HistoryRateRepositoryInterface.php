<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History;

use CurrencyRate\Domain\Collection\HistoryRateRecordCollection;

interface HistoryRateRepositoryInterface
{
    public function findRows(
        string $minimumEffectiveDate,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $currencyIsoCode = null
    ): HistoryRateRecordCollection;

    /**
     * @param list<string> $supportedIsoCodes
     */
    public function cleanupHistory(string $cutoffDate, array $supportedIsoCodes): void;

    public function upsertHistoryRow(
        string $isoCode,
        string $effectiveDate,
        float $mid,
        string $tableNo,
        string $tableType,
        string $dateTime
    ): void;
}
