<?php

declare(strict_types=1);

namespace CurrencyRate\Infrastructure\Repository;

use CurrencyRate\Application\History\HistoryRateRepositoryInterface;
use CurrencyRate\Domain\Collection\HistoryRateRecordCollection;
use CurrencyRate\Infrastructure\Database\DatabaseAdapterInterface;
use CurrencyRate\Infrastructure\Mapper\Db\HistoryRateRecordCollectionMapper;

final class PrestaShopHistoryRateRepository implements HistoryRateRepositoryInterface
{
    public function __construct(
        private DatabaseAdapterInterface $database,
        private HistoryRateRecordCollectionMapper $historyRateRecordCollectionMapper
    )
    {
    }

    public function findRows(
        string $minimumEffectiveDate,
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $currencyIsoCode = null
    ): HistoryRateRecordCollection {
        $query = new \DbQuery();
        $query->select('`effective_date`, `iso_code`, `mid`');
        $query->from('currencyrate_history');
        $query->where(sprintf('`effective_date` >= "%s"', pSQL($minimumEffectiveDate)));

        if ($dateFrom !== null) {
            $query->where(sprintf('`effective_date` >= "%s"', pSQL($dateFrom)));
        }

        if ($dateTo !== null) {
            $query->where(sprintf('`effective_date` <= "%s"', pSQL($dateTo)));
        }

        if ($currencyIsoCode !== null && $currencyIsoCode !== '') {
            $query->where(sprintf('`iso_code` = "%s"', pSQL($currencyIsoCode)));
        }

        $query->orderBy('`effective_date` DESC, `iso_code` ASC');

        return $this->historyRateRecordCollectionMapper->map($this->database->executeQuery($query));
    }

    public function cleanupHistory(string $cutoffDate, array $supportedIsoCodes): void
    {
        if ($supportedIsoCodes === []) {
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
            pSQL($cutoffDate),
            $escapedIsoList
        );

        $this->database->execute($deleteSql);
    }

    public function upsertHistoryRow(
        string $isoCode,
        string $effectiveDate,
        float $mid,
        string $tableNo,
        string $tableType,
        string $dateTime
    ): void {
        $data = [
            'iso_code' => pSQL($isoCode),
            'effective_date' => pSQL($effectiveDate),
            'mid' => $mid,
            'table_no' => pSQL($tableNo),
            'table_type' => pSQL($tableType),
            'date_add' => $dateTime,
            'date_upd' => $dateTime,
        ];

        $this->database->insertOnDuplicateKey('currencyrate_history', $data);
    }
}
