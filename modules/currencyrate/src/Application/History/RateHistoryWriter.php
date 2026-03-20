<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History;

use CurrencyRate\Application\ClockInterface;
use CurrencyRate\Application\CurrencyProviderInterface;
use CurrencyRate\Domain\Collection\NbpTableCollection;
use CurrencyRate\Domain\Dto\NbpRate;
use CurrencyRate\Domain\Dto\NbpTable;
use CurrencyRate\Infrastructure\Log\CurrencyRateDebugLogger;

final class RateHistoryWriter implements RateHistoryWriterInterface
{
    public function __construct(
        private CurrencyProviderInterface $shopCurrencyProvider,
        private ClockInterface $clock
    ) {
    }

    public function replaceThirtyDays(NbpTableCollection $tableCollection, \DateTimeImmutable $cutoffDate): void
    {
        $supportedCurrencies = $this->shopCurrencyProvider->getActiveCurrenciesIndexedByIsoCode();
        $supportedIsoCodes = array_keys($supportedCurrencies);
        if ($supportedIsoCodes === []) {
            CurrencyRateDebugLogger::log('History write aborted: no active currencies');
            return;
        }

        $escapedIsoList = implode(
            ',',
            array_map(
                static fn (string $iso): string => "'" . pSQL($iso) . "'",
                $supportedIsoCodes
            )
        );

        $deleteSql = sprintf(
            'DELETE FROM `%scurrencyrate_history` WHERE `effective_date` < "%s" OR `iso_code` NOT IN (%s)',
            _DB_PREFIX_,
            pSQL($cutoffDate->format('Y-m-d')),
            $escapedIsoList
        );
        CurrencyRateDebugLogger::log('History cleanup query start', [
            'cutoff_date' => $cutoffDate->format('Y-m-d'),
            'supported_currencies_count' => count($supportedIsoCodes),
        ]);
        \Db::getInstance()->execute($deleteSql);

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

                $isoCode = strtoupper($rate->code());
                if (!isset($supportedCurrencies[$isoCode])) {
                    continue;
                }

                $data = [
                    'iso_code' => pSQL($isoCode),
                    'effective_date' => pSQL($table->effectiveDate()),
                    'mid' => (float) $rate->mid(),
                    'table_no' => pSQL($table->number()),
                    'table_type' => pSQL($table->table()),
                    'date_add' => $now,
                    'date_upd' => $now,
                ];

                \Db::getInstance()->insert('currencyrate_history', $data, false, true, \Db::ON_DUPLICATE_KEY);
                $writtenRows++;
            }
        }

        $cache = \Cache::getInstance();
        if ($cache !== null) {
            $cache->delete('currencyrate_history_*');
        }
        \Cache::clean('currencyrate_history_*');
        CurrencyRateDebugLogger::log('History write completed', ['written_rows' => $writtenRows]);
    }
}
