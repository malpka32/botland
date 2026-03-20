<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History;

use CurrencyRate\Application\ClockInterface;
use CurrencyRate\Application\ExchangeRate\CurrencyRateUpdater;
use CurrencyRate\Application\ExchangeRate\ExchangeRateGatewayInterface;
use CurrencyRate\Domain\Collection\NbpTableCollection;
use CurrencyRate\Infrastructure\Log\CurrencyRateDebugLogger;

final class RateHistorySynchronizer
{
    public function __construct(
        private ExchangeRateGatewayInterface $gateway,
        private RateHistoryWriterInterface $historyWriter,
        private CurrencyRateUpdater $currencyRateUpdater,
        private ClockInterface $clock
    ) {
    }

    public function syncLastThirtyDays(): void
    {
        CurrencyRateDebugLogger::log('History synchronization started');
        $endDate = $this->clock->today();
        $startDate = $endDate->modify('-30 days');
        if ($startDate === false) {
            throw new \RuntimeException('Could not build start date.');
        }

        $tableCollectionA = $this->gateway->fetchTableARange($startDate, $endDate);
        $tableCollectionB = $this->gateway->fetchTableBRange($startDate, $endDate);
        CurrencyRateDebugLogger::log('History synchronization fetched tables', [
            'table_a_count' => $tableCollectionA->count(),
            'table_b_count' => $tableCollectionB->count(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ]);
        $combinedCollection = new NbpTableCollection();
        foreach ($tableCollectionA as $tableA) {
            $combinedCollection->add($tableA);
        }
        foreach ($tableCollectionB as $tableB) {
            $combinedCollection->add($tableB);
        }

        $this->historyWriter->replaceThirtyDays($combinedCollection, $startDate);
        $this->currencyRateUpdater->refreshCurrentRates();
        CurrencyRateDebugLogger::log('History synchronization finished');
    }
}
