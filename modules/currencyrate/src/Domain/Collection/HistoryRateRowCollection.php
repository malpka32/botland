<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Collection;

use Countable;
use CurrencyRate\Domain\Dto\HistoryRateRow;
use IteratorAggregate;
use ArrayIterator;
use Traversable;

final class HistoryRateRowCollection implements IteratorAggregate, Countable
{
    /** @var list<HistoryRateRow> */
    private array $items = [];

    public function add(HistoryRateRow $row): void
    {
        $this->items[] = $row;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, HistoryRateRow>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return list<array{effective_date: string, iso_code: string, currency_name: string, mid: float}>
     */
    public function toTemplateArray(): array
    {
        $rows = [];
        foreach ($this->items as $item) {
            $rows[] = $item->toTemplateArray();
        }

        return $rows;
    }
}
