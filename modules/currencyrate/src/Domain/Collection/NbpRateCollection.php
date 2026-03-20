<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Collection;

use Countable;
use CurrencyRate\Domain\Dto\NbpRate;
use IteratorAggregate;
use ArrayIterator;
use Traversable;

final class NbpRateCollection implements IteratorAggregate, Countable
{
    /** @var list<NbpRate> */
    private array $items = [];

    public function add(NbpRate $rate): void
    {
        $this->items[] = $rate;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, NbpRate>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
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
