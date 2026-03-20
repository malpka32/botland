<?php

declare(strict_types=1);

namespace CurrencyRate\Application\Cache;

use CurrencyRate\Domain\Collection\HistoryRateRowCollection;

interface RateHistoryCacheInterface
{
    public function getForContext(
        int $shopId,
        int $languageId,
        ?string $dateFrom,
        ?string $dateTo,
        ?string $currencyIsoCode
    ): ?HistoryRateRowCollection;

    public function storeForContext(
        int $shopId,
        int $languageId,
        ?string $dateFrom,
        ?string $dateTo,
        ?string $currencyIsoCode,
        HistoryRateRowCollection $collection
    ): void;

    public function invalidateAll(): void;
}
