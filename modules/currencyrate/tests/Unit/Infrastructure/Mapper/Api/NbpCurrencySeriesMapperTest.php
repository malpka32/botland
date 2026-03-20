<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Infrastructure\Mapper\Api;

use CurrencyRate\Infrastructure\Mapper\Api\NbpCurrencySeriesMapper;
use PHPUnit\Framework\TestCase;

final class NbpCurrencySeriesMapperTest extends TestCase
{
    public function testMapBuildsSeriesAndSkipsInvalidRateRows(): void
    {
        $json = json_encode([
            'table' => 'A',
            'currency' => 'Euro',
            'code' => 'eur',
            'rates' => [
                ['no' => '12/A/NBP/2026', 'effectiveDate' => '2026-03-20', 'mid' => 4.2],
                ['no' => '', 'effectiveDate' => '2026-03-20', 'mid' => 4.3],
            ],
        ], JSON_THROW_ON_ERROR);

        $series = (new NbpCurrencySeriesMapper())->map($json);

        self::assertSame('A', $series->table());
        self::assertSame('Euro', $series->currency());
        self::assertSame('EUR', $series->code());
        self::assertSame(1, $series->rates()->count());
        self::assertSame(4.2, $series->rates()->last()?->mid());
    }

    public function testMapThrowsOnInvalidJson(): void
    {
        $this->expectException(\RuntimeException::class);
        (new NbpCurrencySeriesMapper())->map('{broken');
    }
}
