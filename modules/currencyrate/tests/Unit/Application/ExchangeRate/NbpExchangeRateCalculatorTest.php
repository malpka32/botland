<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application\ExchangeRate;

use CurrencyRate\Application\ExchangeRate\NbpExchangeRateCalculator;
use CurrencyRate\Application\ExchangeRate\PlnRateLookupStrategyInterface;
use PHPUnit\Framework\TestCase;

final class NbpExchangeRateCalculatorTest extends TestCase
{
    public function testResolvePlnPerCurrencyReturnsZeroForMissingRate(): void
    {
        $calculator = new NbpExchangeRateCalculator(new class implements PlnRateLookupStrategyInterface {
            public function resolve(string $isoCode): ?float
            {
                return null;
            }
        });

        self::assertSame(0.0, $calculator->resolvePlnPerCurrency('EUR'));
    }

    public function testResolvePlnPerCurrencyReturnsZeroForNonPositiveRate(): void
    {
        $calculator = new NbpExchangeRateCalculator(new class implements PlnRateLookupStrategyInterface {
            public function resolve(string $isoCode): ?float
            {
                return 0.0;
            }
        });

        self::assertSame(0.0, $calculator->resolvePlnPerCurrency('EUR'));
    }

    public function testCalculatePrestaConversionRateReturnsOneForSameCurrency(): void
    {
        $calculator = new NbpExchangeRateCalculator(new class implements PlnRateLookupStrategyInterface {
            public function resolve(string $isoCode): ?float
            {
                return 4.22;
            }
        });

        self::assertSame(1.0, $calculator->calculatePrestaConversionRate('usd', 'USD', 4.22));
    }

    public function testCalculatePrestaConversionRateReturnsNullWhenTargetRateCannotBeResolved(): void
    {
        $calculator = new NbpExchangeRateCalculator(new class implements PlnRateLookupStrategyInterface {
            public function resolve(string $isoCode): ?float
            {
                return null;
            }
        });

        self::assertNull($calculator->calculatePrestaConversionRate('EUR', 'PLN', 1.0));
    }

    public function testCalculatePrestaConversionRateCalculatesExpectedRatio(): void
    {
        $calculator = new NbpExchangeRateCalculator(new class implements PlnRateLookupStrategyInterface {
            public function resolve(string $isoCode): ?float
            {
                return match (strtoupper($isoCode)) {
                    'EUR' => 4.0,
                    default => null,
                };
            }
        });

        $rate = $calculator->calculatePrestaConversionRate('EUR', 'PLN', 1.0);

        self::assertNotNull($rate);
        self::assertSame(0.25, $rate);
    }
}
