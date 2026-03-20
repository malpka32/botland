<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application;

use CurrencyRate\Application\SystemClock;
use PHPUnit\Framework\TestCase;

final class SystemClockTest extends TestCase
{
    public function testNowReturnsDateTimeImmutable(): void
    {
        self::assertInstanceOf(\DateTimeImmutable::class, (new SystemClock())->now());
    }

    public function testTodayReturnsMidnight(): void
    {
        self::assertSame('00:00:00', (new SystemClock())->today()->format('H:i:s'));
    }
}
