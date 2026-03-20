<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History;

use CurrencyRate\Domain\Collection\HistoryRateRowCollection;
use CurrencyRate\Infrastructure\Log\CurrencyRateDebugLogger;

final class CachedRateHistoryReader implements RateHistoryReaderInterface
{
    private const CACHE_TTL_SECONDS = 300;

    public function __construct(private RateHistoryReaderInterface $innerReader)
    {
    }

    public function findLastThirtyDays(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $currencyIsoCode = null
    ): HistoryRateRowCollection {
        $context = \Context::getContext();
        $shopId = (int) $context->shop->id;
        $languageId = (int) $context->language->id;
        $cacheKey = 'currencyrate_history_' . $shopId . '_' . $languageId . '_' . md5(
            implode('|', [$dateFrom ?? '', $dateTo ?? '', $currencyIsoCode ?? ''])
        );

        $cache = \Cache::getInstance();
        // if ($cache !== null) {
        //     if ($cached instanceof HistoryRateRowCollection) {
        //         CurrencyRateDebugLogger::log('History cache hit', [
        //             'cache_key' => $cacheKey,
        //             'source' => 'prestashop_backend',
        //         ]);

        //         return $cached;
        //     }
        // }

        if (\Cache::isStored($cacheKey)) {
            CurrencyRateDebugLogger::log('History cache hit', [
                'cache_key' => $cacheKey,
                'source' => 'request_local',
            ]);
            /** @var HistoryRateRowCollection $cached */
            $cached = \Cache::retrieve($cacheKey);
            return $cached;
        }

        CurrencyRateDebugLogger::log('History cache miss', ['cache_key' => $cacheKey]);
        $collection = $this->innerReader->findLastThirtyDays($dateFrom, $dateTo, $currencyIsoCode);
        if ($cache !== null) {
            $cache->set($cacheKey, $collection, self::CACHE_TTL_SECONDS);
        }
        \Cache::store($cacheKey, $collection);
        CurrencyRateDebugLogger::log('History cache stored', [
            'cache_key' => $cacheKey,
            'rows_count' => $collection->count(),
            'ttl_seconds' => self::CACHE_TTL_SECONDS,
        ]);

        return $collection;
    }
}
