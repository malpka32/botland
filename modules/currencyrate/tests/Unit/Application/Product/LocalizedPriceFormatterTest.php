<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application\Product;

use CurrencyRate\Application\Product\LocalizedPriceFormatter;
use PHPUnit\Framework\TestCase;

final class LocalizedPriceFormatterTest extends TestCase
{
    protected function setUp(): void
    {
        \Context::getContext()->currentLocale = null;
    }

    public function testFormatsPriceWithLocaleWhenAvailable(): void
    {
        \Context::getContext()->currentLocale = new class {
            public function formatPrice(float $price, string $isoCode): string
            {
                return sprintf('locale:%s:%s', $isoCode, $price);
            }
        };

        $formatter = new LocalizedPriceFormatter();

        self::assertSame('locale:EUR:123.45', $formatter->format(123.45, 'EUR', 'EUR'));
    }

    public function testFallsBackToNumberFormattingWhenLocaleIsMissing(): void
    {
        $formatter = new LocalizedPriceFormatter();

        self::assertSame('123.46 EUR', $formatter->format(123.456, 'EUR', 'EUR'));
    }
}
