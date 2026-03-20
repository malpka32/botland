<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History;

use CurrencyRate\Application\CurrencyNameResolver;
use CurrencyRate\Application\CurrencyProviderInterface;
use CurrencyRate\Domain\Collection\HistoryRateRowCollection;
use CurrencyRate\Domain\Dto\HistoryRateRow;
use CurrencyRate\Infrastructure\Log\CurrencyRateDebugLogger;

final class RateHistoryReader implements RateHistoryReaderInterface
{
    public function __construct(
        private CurrencyProviderInterface $shopCurrencyProvider,
        private CurrencyNameResolver $currencyNameResolver
    ) {
    }

    public function findLastThirtyDays(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $currencyIsoCode = null
    ): HistoryRateRowCollection {
        $currencies = $this->shopCurrencyProvider->getActiveCurrenciesIndexedByIsoCode();
        if ($currencies === []) {
            CurrencyRateDebugLogger::log('History read aborted: no active currencies');
            return new HistoryRateRowCollection();
        }

        $escapedIsoList = implode(
            ',',
            array_map(
                static fn (string $iso): string => "'" . pSQL($iso) . "'",
                array_keys($currencies)
            )
        );

        $where = [
            sprintf('`iso_code` IN (%s)', $escapedIsoList),
            sprintf('`effective_date` >= "%s"', pSQL((new \DateTimeImmutable('-30 days'))->format('Y-m-d'))),
        ];
        if ($dateFrom !== null) {
            $where[] = sprintf('`effective_date` >= "%s"', pSQL($dateFrom));
        }
        if ($dateTo !== null) {
            $where[] = sprintf('`effective_date` <= "%s"', pSQL($dateTo));
        }
        if ($currencyIsoCode !== null) {
            $where[] = sprintf('`iso_code` = "%s"', pSQL($currencyIsoCode));
        }

        $sql = sprintf(
            'SELECT `effective_date`, `iso_code`, `mid` FROM `%scurrencyrate_history` WHERE %s ORDER BY `effective_date` DESC, `iso_code` ASC',
            _DB_PREFIX_,
            implode(' AND ', $where)
        );

        CurrencyRateDebugLogger::log('History read query', [
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
            'currency' => $currencyIsoCode,
        ]);

        $rows = \Db::getInstance()->executeS($sql) ?: [];
        $collection = new HistoryRateRowCollection();
        foreach ($rows as $row) {
            $iso = strtoupper((string) ($row['iso_code'] ?? ''));
            if (!isset($currencies[$iso])) {
                continue;
            }

            $collection->add(
                new HistoryRateRow(
                    (string) ($row['effective_date'] ?? ''),
                    $iso,
                    $this->currencyNameResolver->resolve($currencies[$iso]),
                    (float) ($row['mid'] ?? 0)
                )
            );
        }

        CurrencyRateDebugLogger::log('History read completed', ['rows_count' => $collection->count()]);

        return $collection;
    }
}
