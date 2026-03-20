<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History;

use CurrencyRate\Application\CurrencyDisplayNameByIsoResolver;
use CurrencyRate\Domain\Collection\HistoryRateRecordCollection;
use CurrencyRate\Domain\Collection\HistoryRateRowCollection;
use CurrencyRate\Domain\Dto\Persistence\HistoryRateRecord;
use CurrencyRate\Domain\Dto\Shop\HistoryRateRow;

final class HistoryRateRowCollectionMapper
{
    public function __construct(private CurrencyDisplayNameByIsoResolver $currencyDisplayNameByIsoResolver)
    {
    }

    public function map(HistoryRateRecordCollection $records): HistoryRateRowCollection
    {
        $collection = new HistoryRateRowCollection();
        foreach ($records as $record) {
            if (!$record instanceof HistoryRateRecord) {
                continue;
            }

            $iso = $record->isoCode();
            $currencyName = $this->currencyDisplayNameByIsoResolver->resolve($iso);

            $collection->add(
                new HistoryRateRow(
                    $record->effectiveDate(),
                    $iso,
                    $currencyName,
                    $record->mid()
                )
            );
        }

        return $collection;
    }
}
