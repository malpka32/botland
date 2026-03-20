<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History;

use CurrencyRate\Application\Cache\RateHistoryCacheInterface;
use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Domain\Collection\HistoryRateRowCollection;

final class CachedRateHistoryReader implements RateHistoryReaderInterface
{
    public function __construct(
        private RateHistoryReaderInterface $innerReader,
        private RateHistoryCacheInterface $cache,
        private DebugLoggerInterface $logger
    ) {
    }

    public function findLastThirtyDays(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $currencyIsoCode = null
    ): HistoryRateRowCollection {
        $context = \Context::getContext();
        $shopId = isset($context->shop) ? (int) $context->shop->id : 0;
        $languageId = isset($context->language) ? (int) $context->language->id : 0;
        if ($shopId <= 0 || $languageId <= 0) {
            $this->logger->log('History cache bypassed: missing context identifiers', [
                'shop_id' => $shopId,
                'language_id' => $languageId,
            ]);

            return $this->innerReader->findLastThirtyDays($dateFrom, $dateTo, $currencyIsoCode);
        }

        $cached = $this->cache->getForContext(
            $shopId,
            $languageId,
            $dateFrom,
            $dateTo,
            $currencyIsoCode
        );
        if ($cached instanceof HistoryRateRowCollection) {
            $this->logger->log('History cache hit', [
                'shop_id' => $shopId,
                'language_id' => $languageId,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'currency' => $currencyIsoCode,
            ]);

            return $cached;
        }

        $this->logger->log('History cache miss', [
            'shop_id' => $shopId,
            'language_id' => $languageId,
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'currency' => $currencyIsoCode,
        ]);

        $collection = $this->innerReader->findLastThirtyDays($dateFrom, $dateTo, $currencyIsoCode);
        $this->cache->storeForContext($shopId, $languageId, $dateFrom, $dateTo, $currencyIsoCode, $collection);
        $this->logger->log('History cache stored', [
            'shop_id' => $shopId,
            'language_id' => $languageId,
            'rows_count' => $collection->count(),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'currency' => $currencyIsoCode,
        ]);

        return $collection;
    }
}
