<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History;

use CurrencyRate\Domain\Collection\HistoryRateRowCollection;

interface RateHistoryReaderInterface
{
    public function findLastThirtyDays(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $currencyIsoCode = null
    ): HistoryRateRowCollection;
}
