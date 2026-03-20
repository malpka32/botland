<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Collection;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * @template T of object
 * @implements IteratorAggregate<int, T>
 */
abstract class AbstractTypedCollection implements IteratorAggregate, Countable
{
    /** @var list<T> */
    protected array $items = [];

    final public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, T>
     */
    final public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return list<T>
     */
    final public function toArray(): array
    {
        return $this->items;
    }

    /**
     * @param T $item
     */
    final protected function addItem(object $item): void
    {
        $this->items[] = $item;
    }
}
