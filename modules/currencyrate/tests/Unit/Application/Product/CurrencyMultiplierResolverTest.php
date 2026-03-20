<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application\Product;

use CurrencyRate\Application\Cache\CurrencyRatesSnapshotCacheInterface;
use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Application\Product\CurrencyMultiplierResolver;
use PHPUnit\Framework\TestCase;

final class CurrencyMultiplierResolverTest extends TestCase
{
    public function testReturnsZeroForInvalidInputs(): void
    {
        $cache = $this->createMock(CurrencyRatesSnapshotCacheInterface::class);
        $cache->expects(self::never())->method('getRateForCurrencyId');

        $logger = $this->createMock(DebugLoggerInterface::class);
        $logger->expects(self::never())->method('log');

        $resolver = new CurrencyMultiplierResolver($cache, $logger);
        $currency = new \Currency();
        $currency->id = 0;

        self::assertSame(0.0, $resolver->resolveForCurrency($currency, 1.0));
        self::assertSame(0.0, $resolver->resolveForCurrency($currency, 0.0));
    }

    public function testReturnsCachedMultiplierWhenAvailable(): void
    {
        $cache = $this->createMock(CurrencyRatesSnapshotCacheInterface::class);
        $cache->expects(self::once())
            ->method('getRateForCurrencyId')
            ->with(10)
            ->willReturn(1.25);
        $cache->expects(self::never())->method('storeRateForCurrencyId');

        $logger = $this->createMock(DebugLoggerInterface::class);
        $logger->expects(self::never())->method('log');

        $resolver = new CurrencyMultiplierResolver($cache, $logger);
        $currency = new \Currency();
        $currency->id = 10;
        $currency->conversion_rate = 2.0;

        self::assertSame(1.25, $resolver->resolveForCurrency($currency, 1.0));
    }

    public function testBuildsAndStoresMultiplierFromCurrencyRate(): void
    {
        $cache = $this->createMock(CurrencyRatesSnapshotCacheInterface::class);
        $cache->expects(self::once())
            ->method('getRateForCurrencyId')
            ->with(10)
            ->willReturn(null);
        $cache->expects(self::once())
            ->method('storeRateForCurrencyId')
            ->with(10, 2.5);

        $logger = $this->createMock(DebugLoggerInterface::class);
        $logger->expects(self::once())
            ->method('log')
            ->with('Currency multiplier loaded from database', [
                'currency_id' => 10,
                'conversion_rate' => 5.0,
                'default_rate' => 2.0,
                'multiplier' => 2.5,
            ]);

        $resolver = new CurrencyMultiplierResolver($cache, $logger);
        $currency = new \Currency();
        $currency->id = 10;
        $currency->conversion_rate = 5.0;

        self::assertSame(2.5, $resolver->resolveForCurrency($currency, 2.0));
    }
}
