<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Infrastructure\Mapper\Api;

use CurrencyRate\Infrastructure\Mapper\Api\NbpTableMapper;
use PHPUnit\Framework\TestCase;

final class NbpTableMapperTest extends TestCase
{
    public function testMapCollectionMapsValidRowsAndSkipsInvalidRates(): void
    {
        $json = json_encode([
            [
                'table' => 'A',
                'no' => '12/A/NBP/2026',
                'effectiveDate' => '2026-03-20',
                'rates' => [
                    ['currency' => 'Euro', 'code' => 'eur', 'mid' => 4.2],
                    ['currency' => '', 'code' => 'XXX', 'mid' => 0],
                ],
            ],
            'invalid-row',
        ], JSON_THROW_ON_ERROR);

        $collection = (new NbpTableMapper())->mapCollection($json);

        self::assertSame(1, $collection->count());
        $first = $collection->first();
        self::assertNotNull($first);
        self::assertSame('A', $first->table());
        self::assertSame(1, $first->rates()->count());
        self::assertSame('EUR', $first->rates()->findByCode('EUR')?->code());
    }

    public function testMapCollectionThrowsOnInvalidJson(): void
    {
        $this->expectException(\RuntimeException::class);
        (new NbpTableMapper())->mapCollection('not json');
    }
}
