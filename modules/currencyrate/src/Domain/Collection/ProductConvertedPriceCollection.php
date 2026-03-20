<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Collection;

use CurrencyRate\Domain\Dto\Shop\ProductConvertedPrice;

/**
 * @extends AbstractTypedCollection<ProductConvertedPrice>
 */
final class ProductConvertedPriceCollection extends AbstractTypedCollection
{
    public function add(ProductConvertedPrice $row): void
    {
        $this->addItem($row);
    }
}
