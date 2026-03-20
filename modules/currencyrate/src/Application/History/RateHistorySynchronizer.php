<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History;

use CurrencyRate\Application\ClockInterface;
use CurrencyRate\Application\Log\DebugLoggerInterface;

final class RateHistorySynchronizer
{
    public function __construct(
        private HistoryTablesFetcher $historyTablesFetcher,
        private RateHistoryWriterInterface $historyWriter,
        private ClockInterface $clock,
        private DebugLoggerInterface $logger
    ) {
    }

    public function syncHistoryLastThirtyDays(): void
    {
        $this->logger->log('History synchronization started');
        $endDate = $this->clock->today();
        $startDate = HistoryDateRange::defaultStartDateFrom($endDate);

        $tables = $this->historyTablesFetcher->fetch($startDate, $endDate);
        $this->logger->log('History synchronization fetched tables', [
            'tables_count' => $tables->count(),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
        ]);

        $this->historyWriter->replaceThirtyDays($tables, $startDate);
        $this->logger->log('History synchronization finished');
    }
}
