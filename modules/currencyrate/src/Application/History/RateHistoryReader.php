<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History;

use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Application\Support\CurrencyIsoCode;
use CurrencyRate\Domain\Collection\HistoryRateRowCollection;

final class RateHistoryReader implements RateHistoryReaderInterface
{
    public function __construct(
        private HistoryRateRowCollectionMapper $historyRateRowCollectionMapper,
        private DebugLoggerInterface $logger,
        private HistoryRateRepositoryInterface $historyRateRepository
    ) {
    }

    public function findLastThirtyDays(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $currencyIsoCode = null
    ): HistoryRateRowCollection {
        $normalizedCurrencyIsoCode = $this->normalizeCurrencyIsoCode($currencyIsoCode);

        $this->logger->log('History read query', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'currency' => $normalizedCurrencyIsoCode,
        ]);

        $records = $this->historyRateRepository->findRows(
            $this->minimumEffectiveDate(),
            $dateFrom,
            $dateTo,
            $normalizedCurrencyIsoCode
        );
        $collection = $this->historyRateRowCollectionMapper->map($records);

        $this->logger->log('History read completed', ['rows_count' => $collection->count()]);

        return $collection;
    }

    private function minimumEffectiveDate(): string
    {
        return HistoryDateRange::defaultStartDateFrom(HistoryDateRange::today())->format('Y-m-d');
    }

    private function normalizeCurrencyIsoCode(?string $currencyIsoCode): ?string
    {
        if ($currencyIsoCode === null) {
            return null;
        }

        $normalizedCurrencyIsoCode = CurrencyIsoCode::normalize($currencyIsoCode);

        return $normalizedCurrencyIsoCode !== '' ? $normalizedCurrencyIsoCode : null;
    }
}
