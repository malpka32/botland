<?php

declare(strict_types=1);

namespace CurrencyRate\Infrastructure\Mapper\Db;

use CurrencyRate\Application\Support\CurrencyIsoCode;
use CurrencyRate\Domain\Collection\HistoryRateRecordCollection;
use CurrencyRate\Domain\Dto\Persistence\HistoryRateRecord;

final class HistoryRateRecordCollectionMapper
{
    /**
     * @param list<array<string, mixed>> $rows
     */
    public function map(array $rows): HistoryRateRecordCollection
    {
        $collection = new HistoryRateRecordCollection();
        foreach ($rows as $row) {
            $collection->add(
                new HistoryRateRecord(
                    (string) ($row['effective_date'] ?? ''),
                    CurrencyIsoCode::normalize((string) ($row['iso_code'] ?? '')),
                    (float) ($row['mid'] ?? 0.0)
                )
            );
        }

        return $collection;
    }
}
