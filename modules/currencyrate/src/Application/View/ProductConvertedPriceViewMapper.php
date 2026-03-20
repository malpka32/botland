<?php

declare(strict_types=1);

namespace CurrencyRate\Application\View;

use CurrencyRate\Domain\Collection\ProductConvertedPriceCollection;
use CurrencyRate\Domain\Dto\Shop\ProductConvertedPrice;

final class ProductConvertedPriceViewMapper
{
    /**
     * @return list<array{iso_code: string, name: string, symbol: string, formatted_price: string}>
     */
    public function mapCollection(ProductConvertedPriceCollection $collection): array
    {
        $rows = [];
        foreach ($collection as $item) {
            if (!$item instanceof ProductConvertedPrice) {
                continue;
            }

            $rows[] = [
                'iso_code' => $item->isoCode(),
                'name' => $item->name(),
                'symbol' => $item->symbol(),
                'formatted_price' => $item->formattedPrice(),
            ];
        }

        return $rows;
    }
}
