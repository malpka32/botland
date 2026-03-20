<?php

declare(strict_types=1);

namespace CurrencyRate\Application\Cache;

use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Application\Shared\CurrencyRateModuleConfig;

final class CurrencyRatesSnapshotCache implements CurrencyRatesSnapshotCacheInterface
{
    private const CACHE_KEY_PREFIX = 'currencyrate_rate_';

    public function __construct(private DebugLoggerInterface $logger)
    {
    }

    public function getRateForCurrencyId(int $currencyId): ?float
    {
        if ($currencyId <= 0) {
            return null;
        }

        $cacheKey = $this->buildCacheKey($currencyId);
        $cache = \Cache::getInstance();

        if ($cache === null) {
            return null;
        }

        $cachedRate = $cache->get($cacheKey);
        if (is_numeric($cachedRate)) {
            $normalizedRate = (float) $cachedRate;
            if ($normalizedRate > 0.0) {
                $this->logger->log('Currency multiplier loaded from cache', [
                    'cache_key' => $cacheKey,
                    'currency_id' => $currencyId,
                    'multiplier' => $normalizedRate,
                ]);
                return $normalizedRate;
            }
        }

        return null;
    }

    public function storeRateForCurrencyId(int $currencyId, float $multiplier): void
    {
        if ($currencyId <= 0 || $multiplier <= 0.0) {
            return;
        }

        $cacheKey = $this->buildCacheKey($currencyId);
        $cache = \Cache::getInstance();

        if ($cache === null) {
            return;
        }

        $cache->set($cacheKey, $multiplier, CurrencyRateModuleConfig::CACHE_TTL_SECONDS);

        $this->logger->log('Currency rate cache stored', [
            'cache_key' => $cacheKey,
            'currency_id' => $currencyId,
            'multiplier' => $multiplier,
            'ttl_seconds' => CurrencyRateModuleConfig::CACHE_TTL_SECONDS,
        ]);
    }

    public function invalidateAll(): void
    {
        $cache = \Cache::getInstance();
        if ($cache === null) {
            return;
        }

        $cache->delete(self::CACHE_KEY_PREFIX . '*');

        $this->logger->log('Currency rate cache invalidated', [
            'cache_key_pattern' => self::CACHE_KEY_PREFIX . '*',
        ]);
    }

    private function buildCacheKey(int $currencyId): string
    {
        return self::CACHE_KEY_PREFIX . $currencyId;
    }
}
