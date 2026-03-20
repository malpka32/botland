<?php

declare(strict_types=1);

namespace CurrencyRate\Application\Cache;

use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Application\Shared\CurrencyRateModuleConfig;
use CurrencyRate\Domain\Collection\HistoryRateRowCollection;

final class RateHistoryCache implements RateHistoryCacheInterface
{
    private const CACHE_KEY_PREFIX = 'currencyrate_history_';

    public function __construct(private DebugLoggerInterface $logger)
    {
    }

    public function getForContext(
        int $shopId,
        int $languageId,
        ?string $dateFrom,
        ?string $dateTo,
        ?string $currencyIsoCode
    ): ?HistoryRateRowCollection {
        $cacheKey = $this->buildCacheKey($shopId, $languageId, $dateFrom, $dateTo, $currencyIsoCode);
        $cache = \Cache::getInstance();

        if ($cache === null) {
            return null;
        }

        $cached = $cache->get($cacheKey);
        if ($cached instanceof HistoryRateRowCollection) {
            $this->logger->log('History cache hit', [
                'cache_key' => $cacheKey,
            ]);

            return $cached;
        }

        $this->logger->log('History cache miss', ['cache_key' => $cacheKey]);

        return null;
    }

    public function storeForContext(
        int $shopId,
        int $languageId,
        ?string $dateFrom,
        ?string $dateTo,
        ?string $currencyIsoCode,
        HistoryRateRowCollection $collection
    ): void {
        $cacheKey = $this->buildCacheKey($shopId, $languageId, $dateFrom, $dateTo, $currencyIsoCode);
        $cache = \Cache::getInstance();

        if ($cache === null) {
            return;
        }

        $cache->set($cacheKey, $collection, CurrencyRateModuleConfig::CACHE_TTL_SECONDS);

        $this->logger->log('History cache stored', [
            'cache_key' => $cacheKey,
            'rows_count' => $collection->count(),
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
    }

    private function buildCacheKey(
        int $shopId,
        int $languageId,
        ?string $dateFrom,
        ?string $dateTo,
        ?string $currencyIsoCode
    ): string {
        return self::CACHE_KEY_PREFIX . $shopId . '_' . $languageId . '_' . md5(
            implode('|', [$dateFrom ?? '', $dateTo ?? '', $currencyIsoCode ?? ''])
        );
    }
}
