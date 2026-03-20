<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Collection;

use CurrencyRate\Domain\Dto\Persistence\HistoryRateRecord;

/**
 * @extends AbstractTypedCollection<HistoryRateRecord>
 */
final class HistoryRateRecordCollection extends AbstractTypedCollection
{
    public function add(HistoryRateRecord $record): void
    {
        $this->addItem($record);
    }
}
