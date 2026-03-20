<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Infrastructure\Mapper\Db;

use CurrencyRate\Infrastructure\Mapper\Db\HistoryRateRecordCollectionMapper;
use PHPUnit\Framework\TestCase;

final class HistoryRateRecordCollectionMapperTest extends TestCase
{
    public function testMapNormalizesIsoCodeAndCastsValues(): void
    {
        $rows = [
            ['effective_date' => '2026-03-20', 'iso_code' => 'eur', 'mid' => '4.21'],
            ['effective_date' => '2026-03-19', 'iso_code' => ' usd ', 'mid' => 3.99],
        ];

        $collection = (new HistoryRateRecordCollectionMapper())->map($rows);
        $items = $collection->toArray();

        self::assertSame(2, $collection->count());
        self::assertSame('EUR', $items[0]->isoCode());
        self::assertSame(4.21, $items[0]->mid());
        self::assertSame('USD', $items[1]->isoCode());
    }
}
