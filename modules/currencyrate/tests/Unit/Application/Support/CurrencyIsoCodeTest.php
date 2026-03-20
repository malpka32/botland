<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application\Support;

use CurrencyRate\Application\Support\CurrencyIsoCode;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class CurrencyIsoCodeTest extends TestCase
{
    #[DataProvider('normalizeProvider')]
    public function testNormalize(string $input, string $expected): void
    {
        self::assertSame($expected, CurrencyIsoCode::normalize($input));
    }

    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function normalizeProvider(): iterable
    {
        yield 'lowercase' => ['usd', 'USD'];
        yield 'already uppercase' => ['EUR', 'EUR'];
        yield 'with spaces' => ['  pln  ', 'PLN'];
        yield 'mixed casing' => ['gBp', 'GBP'];
    }
}
