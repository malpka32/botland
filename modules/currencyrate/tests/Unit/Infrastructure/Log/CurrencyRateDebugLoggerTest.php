<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Infrastructure\Log;

use CurrencyRate\Infrastructure\Log\CurrencyRateDebugLogger;
use PHPUnit\Framework\TestCase;

final class CurrencyRateDebugLoggerTest extends TestCase
{
    protected function setUp(): void
    {
        \Configuration::$values = [];
        \PrestaShopLogger::$entries = [];
    }

    public function testIsEnabledReflectsConfiguration(): void
    {
        \Configuration::$values[CurrencyRateDebugLogger::DEBUG_ENABLED_KEY] = '1';
        self::assertTrue(CurrencyRateDebugLogger::isEnabled());

        \Configuration::$values[CurrencyRateDebugLogger::DEBUG_ENABLED_KEY] = '0';
        self::assertFalse(CurrencyRateDebugLogger::isEnabled());
    }

    public function testLogWritesEntryWhenEnabled(): void
    {
        \Configuration::$values[CurrencyRateDebugLogger::DEBUG_ENABLED_KEY] = '1';

        CurrencyRateDebugLogger::log('message', ['iso' => 'EUR']);

        self::assertCount(1, \PrestaShopLogger::$entries);
        self::assertStringContainsString('[currencyrate][debug] message', \PrestaShopLogger::$entries[0]['message']);
        self::assertStringContainsString('"iso":"EUR"', \PrestaShopLogger::$entries[0]['message']);
    }

    public function testLogDoesNothingWhenDisabled(): void
    {
        \Configuration::$values[CurrencyRateDebugLogger::DEBUG_ENABLED_KEY] = '0';

        CurrencyRateDebugLogger::log('message');

        self::assertSame([], \PrestaShopLogger::$entries);
    }
}
