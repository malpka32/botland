<?php

declare(strict_types=1);

namespace CurrencyRate\Application\Cache;

interface CurrencyRatesSnapshotCacheInterface
{
    public function getRateForCurrencyId(int $currencyId): ?float;

    public function storeRateForCurrencyId(int $currencyId, float $multiplier): void;

    public function invalidateAll(): void;
}
