<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Collection;

use CurrencyRate\Domain\Dto\Shop\HistoryRateRow;

/**
 * @extends AbstractTypedCollection<HistoryRateRow>
 */
final class HistoryRateRowCollection extends AbstractTypedCollection
{
    public function add(HistoryRateRow $row): void
    {
        $this->addItem($row);
    }
}
