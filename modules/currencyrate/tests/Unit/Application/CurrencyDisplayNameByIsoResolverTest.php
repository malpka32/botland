<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application;

use CurrencyRate\Application\CurrencyDisplayNameByIsoResolver;
use CurrencyRate\Application\CurrencyNameResolver;
use CurrencyRate\Application\CurrencyProviderInterface;
use PHPUnit\Framework\TestCase;

final class CurrencyDisplayNameByIsoResolverTest extends TestCase
{
    public function testResolveFallsBackToIsoCodeAndUsesCache(): void
    {
        $provider = new class implements CurrencyProviderInterface {
            public int $calls = 0;
            public function getActiveCurrenciesIndexedByIsoCode(): array
            {
                ++$this->calls;
                return [];
            }
        };

        $service = new CurrencyDisplayNameByIsoResolver($provider, new CurrencyNameResolver());

        self::assertSame('CHF', $service->resolve('CHF'));
        self::assertSame('EUR', $service->resolve('EUR'));
        self::assertSame(1, $provider->calls);
    }
}
