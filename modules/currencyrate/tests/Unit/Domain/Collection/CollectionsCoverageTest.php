<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Domain\Collection;

use CurrencyRate\Domain\Collection\HistoryRateRecordCollection;
use CurrencyRate\Domain\Collection\HistoryRateRowCollection;
use CurrencyRate\Domain\Collection\NbpRateCollection;
use CurrencyRate\Domain\Collection\NbpSeriesRateCollection;
use CurrencyRate\Domain\Collection\NbpTableCollection;
use CurrencyRate\Domain\Collection\ProductConvertedPriceCollection;
use CurrencyRate\Domain\Dto\HistoryRateRow;
use CurrencyRate\Domain\Dto\Api\NbpRate;
use CurrencyRate\Domain\Dto\Api\NbpSeriesRate;
use CurrencyRate\Domain\Dto\Api\NbpTable;
use CurrencyRate\Domain\Dto\Persistence\HistoryRateRecord;
use CurrencyRate\Domain\Dto\ProductConvertedPrice;
use PHPUnit\Framework\TestCase;

final class CollectionsCoverageTest extends TestCase
{
    public function testNbpSeriesRateCollection(): void
    {
        $collection = new NbpSeriesRateCollection();
        self::assertSame(0, $collection->count());
        self::assertNull($collection->last());

        $rate = new NbpSeriesRate('1/A/NBP/2026', '2026-03-20', 4.2);
        $collection->add($rate);

        self::assertSame(1, $collection->count());
        self::assertSame($rate, $collection->last());
        self::assertCount(1, iterator_to_array($collection));
    }

    public function testNbpRateCollection(): void
    {
        $collection = new NbpRateCollection();
        $eur = new NbpRate('Euro', 'EUR', 4.2);
        $collection->add($eur);

        self::assertSame(1, $collection->count());
        self::assertSame($eur, $collection->findByCode('eur'));
        self::assertNull($collection->findByCode('USD'));
        self::assertCount(1, iterator_to_array($collection));
    }

    public function testNbpTableCollection(): void
    {
        $collection = new NbpTableCollection();
        self::assertNull($collection->first());

        $rates = new NbpRateCollection();
        $table = new NbpTable('A', '1/A/NBP/2026', '2026-03-20', $rates);
        $collection->add($table);

        self::assertSame(1, $collection->count());
        self::assertSame($table, $collection->first());
        self::assertCount(1, iterator_to_array($collection));
    }

    public function testHistoryRateRowCollection(): void
    {
        $collection = new HistoryRateRowCollection();
        $row = new HistoryRateRow('2026-03-20', 'EUR', 'Euro', 4.2);
        $collection->add($row);

        self::assertSame(1, $collection->count());
        self::assertCount(1, iterator_to_array($collection));
        self::assertSame(
            [['effective_date' => '2026-03-20', 'iso_code' => 'EUR', 'currency_name' => 'Euro', 'mid' => 4.2]],
            $collection->toTemplateArray()
        );
    }

    public function testProductConvertedPriceCollection(): void
    {
        $collection = new ProductConvertedPriceCollection();
        $row = new ProductConvertedPrice('USD', 'US Dollar', '$', '12.34 $');
        $collection->add($row);

        self::assertSame(1, $collection->count());
        self::assertCount(1, iterator_to_array($collection));
        self::assertSame(
            [['iso_code' => 'USD', 'name' => 'US Dollar', 'symbol' => '$', 'formatted_price' => '12.34 $']],
            $collection->toTemplateArray()
        );
    }

    public function testHistoryRateRecordCollectionViaAbstractTypedCollection(): void
    {
        $collection = new HistoryRateRecordCollection();
        $record = new HistoryRateRecord('2026-03-20', 'EUR', 4.2);
        $collection->add($record);

        self::assertSame(1, $collection->count());
        self::assertSame([$record], $collection->toArray());
        self::assertCount(1, iterator_to_array($collection));
    }
}
