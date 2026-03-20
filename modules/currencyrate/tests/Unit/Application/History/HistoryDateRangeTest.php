<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application\History;

use CurrencyRate\Application\History\HistoryDateRange;
use CurrencyRate\Application\Shared\CurrencyRateModuleConfig;
use PHPUnit\Framework\TestCase;

final class HistoryDateRangeTest extends TestCase
{
    public function testDefaultStartDateFromSubtractsConfiguredHistoryWindow(): void
    {
        $endDate = new \DateTimeImmutable('2026-03-20');

        $actual = HistoryDateRange::defaultStartDateFrom($endDate);

        $expected = $endDate->modify(sprintf('-%d days', CurrencyRateModuleConfig::HISTORY_WINDOW_DAYS));
        self::assertInstanceOf(\DateTimeImmutable::class, $expected);
        self::assertSame($expected->format('Y-m-d'), $actual->format('Y-m-d'));
    }

    public function testTodayReturnsDateWithoutTimeComponent(): void
    {
        $today = HistoryDateRange::today();

        self::assertSame('00:00:00', $today->format('H:i:s'));
    }
}
