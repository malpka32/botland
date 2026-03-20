<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Collection;

use Countable;
use CurrencyRate\Domain\Dto\NbpTable;
use IteratorAggregate;
use ArrayIterator;
use Traversable;

final class NbpTableCollection implements IteratorAggregate, Countable
{
    /** @var list<NbpTable> */
    private array $items = [];

    public function add(NbpTable $table): void
    {
        $this->items[] = $table;
    }

    public function count(): int
    {
        return count($this->items);
    }

    public function first(): ?NbpTable
    {
        return $this->items[0] ?? null;
    }

    /**
     * @return Traversable<int, NbpTable>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }
}
