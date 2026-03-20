<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application\View;

use CurrencyRate\Application\View\HistoryRateRowViewMapper;
use CurrencyRate\Application\View\ProductConvertedPriceViewMapper;
use CurrencyRate\Domain\Collection\HistoryRateRowCollection;
use CurrencyRate\Domain\Collection\ProductConvertedPriceCollection;
use CurrencyRate\Domain\Dto\Shop\HistoryRateRow;
use CurrencyRate\Domain\Dto\Shop\ProductConvertedPrice;
use PHPUnit\Framework\TestCase;

final class ViewMappersCoverageTest extends TestCase
{
    public function testHistoryRateRowViewMapperMapsCollection(): void
    {
        $collection = new HistoryRateRowCollection();
        $collection->add(new HistoryRateRow('2026-03-20', 'EUR', 'Euro', 4.2));

        $rows = (new HistoryRateRowViewMapper())->mapCollection($collection);

        self::assertSame(
            [['effective_date' => '2026-03-20', 'iso_code' => 'EUR', 'currency_name' => 'Euro', 'mid' => 4.2]],
            $rows
        );
    }

    public function testProductConvertedPriceViewMapperMapsCollection(): void
    {
        $collection = new ProductConvertedPriceCollection();
        $collection->add(new ProductConvertedPrice('USD', 'US Dollar', '$', '12.34 $'));

        $rows = (new ProductConvertedPriceViewMapper())->mapCollection($collection);

        self::assertSame(
            [['iso_code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'formatted_price' => '12.34 $']],
            $rows
        );
    }
}
