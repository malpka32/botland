<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application\Shared;

use CurrencyRate\Application\Shared\CurrencyRateModuleConfig;
use PHPUnit\Framework\TestCase;

final class CurrencyRateModuleConfigTest extends TestCase
{
    public function testConfigurationConstantsHaveExpectedValues(): void
    {
        self::assertSame(30, CurrencyRateModuleConfig::HISTORY_WINDOW_DAYS);
        self::assertSame(300, CurrencyRateModuleConfig::CACHE_TTL_SECONDS);
        self::assertSame(6, CurrencyRateModuleConfig::PRICE_PRECISION);
        self::assertSame(0.000001, CurrencyRateModuleConfig::RATE_UPDATE_EPSILON);
    }
}
