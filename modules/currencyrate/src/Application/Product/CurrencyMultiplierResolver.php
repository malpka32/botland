<?php

declare(strict_types=1);

namespace CurrencyRate\Application\Product;

use CurrencyRate\Application\Cache\CurrencyRatesSnapshotCacheInterface;
use CurrencyRate\Application\Log\DebugLoggerInterface;

final class CurrencyMultiplierResolver
{
    public function __construct(
        private CurrencyRatesSnapshotCacheInterface $currencyRatesSnapshotCache,
        private DebugLoggerInterface $logger
    )
    {
    }

    public function resolveForCurrency(\Currency $currency, float $defaultRate): float
    {
        $currencyId = (int) $currency->id;
        if ($currencyId <= 0 || $defaultRate <= 0.0) {
            return 0.0;
        }

        $cachedRate = $this->currencyRatesSnapshotCache->getRateForCurrencyId($currencyId);
        if ($cachedRate !== null && $cachedRate > 0.0) {
            return $cachedRate;
        }

        $conversionRate = (float) $currency->conversion_rate;
        if ($conversionRate <= 0.0) {
            return 0.0;
        }

        $multiplier = $conversionRate / $defaultRate;
        $this->logger->log('Currency multiplier loaded from database', [
            'currency_id' => $currencyId,
            'conversion_rate' => $conversionRate,
            'default_rate' => $defaultRate,
            'multiplier' => $multiplier,
        ]);
        $this->currencyRatesSnapshotCache->storeRateForCurrencyId($currencyId, $multiplier);

        return $multiplier;
    }
}
