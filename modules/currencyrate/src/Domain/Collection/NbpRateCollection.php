<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Collection;

use CurrencyRate\Domain\Dto\Api\NbpRate;

/**
 * @extends AbstractTypedCollection<NbpRate>
 */
final class NbpRateCollection extends AbstractTypedCollection
{
    public function add(NbpRate $rate): void
    {
        $this->addItem($rate);
    }

    public function findByCode(string $code): ?NbpRate
    {
        foreach ($this->items as $item) {
            if (strcasecmp($item->code(), $code) === 0) {
                return $item;
            }
        }

        return null;
    }
}
