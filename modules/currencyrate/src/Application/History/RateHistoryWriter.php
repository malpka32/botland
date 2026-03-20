<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History;

use CurrencyRate\Application\Cache\RateHistoryCacheInterface;
use CurrencyRate\Application\ClockInterface;
use CurrencyRate\Application\CurrencyProviderInterface;
use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Application\Support\CurrencyIsoCode;
use CurrencyRate\Domain\Collection\NbpTableCollection;
use CurrencyRate\Domain\Dto\Api\NbpRate;
use CurrencyRate\Domain\Dto\Api\NbpTable;

final class RateHistoryWriter implements RateHistoryWriterInterface
{
    public function __construct(
        private CurrencyProviderInterface $shopCurrencyProvider,
        private ClockInterface $clock,
        private RateHistoryCacheInterface $rateHistoryCache,
        private DebugLoggerInterface $logger,
        private HistoryRateRepositoryInterface $historyRateRepository
    ) {
    }

    public function replaceThirtyDays(NbpTableCollection $tableCollection, \DateTimeImmutable $cutoffDate): void
    {
        $supportedCurrencies = $this->shopCurrencyProvider->getActiveCurrenciesIndexedByIsoCode();
        $supportedIsoCodes = array_keys($supportedCurrencies);
        if ($supportedIsoCodes === []) {
            $this->logger->log('History write aborted: no active currencies');
            return;
        }

        $cutoffDateString = $cutoffDate->format('Y-m-d');
        $this->logger->log('History cleanup query start', [
            'cutoff_date' => $cutoffDateString,
            'supported_currencies_count' => count($supportedIsoCodes),
        ]);
        $this->historyRateRepository->cleanupHistory($cutoffDateString, $supportedIsoCodes);

        $now = $this->clock->now()->format('Y-m-d H:i:s');
        $writtenRows = 0;
        foreach ($tableCollection as $table) {
            if (!$table instanceof NbpTable) {
                continue;
            }

            foreach ($table->rates() as $rate) {
                if (!$rate instanceof NbpRate) {
                    continue;
                }

                $isoCode = CurrencyIsoCode::normalize($rate->code());
                if (!isset($supportedCurrencies[$isoCode])) {
                    continue;
                }

                $this->historyRateRepository->upsertHistoryRow(
                    $isoCode,
                    $table->effectiveDate(),
                    (float) $rate->mid(),
                    $table->number(),
                    $table->table(),
                    $now
                );
                $writtenRows++;
            }
        }

        $this->rateHistoryCache->invalidateAll();
        $this->logger->log('History write completed', ['written_rows' => $writtenRows]);
    }
}
