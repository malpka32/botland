<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Collection;

use Countable;
use CurrencyRate\Domain\Dto\ProductConvertedPrice;
use IteratorAggregate;
use ArrayIterator;
use Traversable;

final class ProductConvertedPriceCollection implements IteratorAggregate, Countable
{
    /** @var list<ProductConvertedPrice> */
    private array $items = [];

    public function add(ProductConvertedPrice $row): void
    {
        $this->items[] = $row;
    }

    public function count(): int
    {
        return count($this->items);
    }

    /**
     * @return Traversable<int, ProductConvertedPrice>
     */
    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->items);
    }

    /**
     * @return list<array{iso_code: string, name: string, symbol: string, formatted_price: string}>
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
