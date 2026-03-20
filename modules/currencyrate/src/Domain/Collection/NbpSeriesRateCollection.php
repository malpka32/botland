<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Collection;

use Countable;
use CurrencyRate\Domain\Dto\NbpSeriesRate;
use IteratorAggregate;
use ArrayIterator;
use Traversable;

final class NbpSeriesRateCollection implements IteratorAggregate, Countable
{
    /** @var list<NbpSeriesRate> */
    private array $items = [];

    public function add(NbpSeriesRate $rate): void
    {
        $this->items[] = $rate;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function last(): ?NbpSeriesRate
    {
        if ($this->items === []) {
            return null;
        }

        return $this->items[array_key_last($this->items)];
    }

    /**
     * @return Traversable<int, NbpSeriesRate>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
