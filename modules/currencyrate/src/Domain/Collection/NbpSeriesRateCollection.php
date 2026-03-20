<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Collection;

use CurrencyRate\Domain\Dto\Api\NbpSeriesRate;

/**
 * @extends AbstractTypedCollection<NbpSeriesRate>
 */
final class NbpSeriesRateCollection extends AbstractTypedCollection
{
    public function add(NbpSeriesRate $rate): void
    {
        $this->addItem($rate);
    }

    public function last(): ?NbpSeriesRate
    {
        if ($this->items === []) {
            return null;
        }

        return $this->items[array_key_last($this->items)];
    }
}
