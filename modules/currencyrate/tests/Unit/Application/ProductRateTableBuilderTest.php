<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application;

use CurrencyRate\Application\CurrencyNameResolver;
use CurrencyRate\Application\CurrencyProviderInterface;
use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Application\Product\CurrencyMultiplierResolver;
use CurrencyRate\Application\Product\LocalizedPriceFormatter;
use CurrencyRate\Application\Product\ProductBasePriceProvider;
use CurrencyRate\Application\ProductRateTableBuilder;
use PHPUnit\Framework\TestCase;

final class ProductRateTableBuilderTest extends TestCase
{
    protected function setUp(): void
    {
        $context = \Context::getContext();
        $context->language = (object) ['id' => 1];
        $context->currency = null;
        \Currency::$defaultCurrency = null;
    }

    public function testReturnsEmptyCollectionWhenBasePriceCannotBeResolved(): void
    {
        $builder = new ProductRateTableBuilder(
            $this->createProvider([]),
            new CurrencyNameResolver(),
            $this->createBasePriceProvider(null),
            $this->createMultiplierResolver(1.0),
            $this->createPriceFormatter(),
            $this->createLogger()
        );

        self::assertSame(0, $builder->buildForProduct(10)->count());
    }

    public function testSkipsCurrenciesWithInvalidIdentifier(): void
    {
        $defaultCurrency = new \Currency();
        $defaultCurrency->id = 1;
        $defaultCurrency->iso_code = 'PLN';
        $defaultCurrency->conversion_rate = 1.0;
        \Currency::$defaultCurrency = $defaultCurrency;

        $invalidCurrency = new \Currency();
        $invalidCurrency->id = 0;
        $invalidCurrency->iso_code = 'EUR';
        $invalidCurrency->symbol = 'EUR';
        $invalidCurrency->name = 'Euro';

        $builder = new ProductRateTableBuilder(
            $this->createProvider(['EUR' => $invalidCurrency]),
            new CurrencyNameResolver(),
            $this->createBasePriceProvider(100.0),
            $this->createMultiplierResolver(1.0),
            $this->createPriceFormatter(),
            $this->createLogger()
        );

        self::assertSame(0, $builder->buildForProduct(10)->count());
    }

    public function testBuildsRowsAndSkipsCurrentlySelectedCurrency(): void
    {
        $defaultCurrency = new \Currency();
        $defaultCurrency->id = 1;
        $defaultCurrency->iso_code = 'PLN';
        $defaultCurrency->conversion_rate = 1.0;
        \Currency::$defaultCurrency = $defaultCurrency;

        $eur = new \Currency();
        $eur->id = 2;
        $eur->iso_code = 'eur';
        $eur->symbol = 'EUR';
        $eur->name = 'Euro';

        $usd = new \Currency();
        $usd->id = 3;
        $usd->iso_code = 'usd';
        $usd->symbol = 'USD';
        $usd->name = 'US Dollar';

        \Context::getContext()->currency = $usd;

        $builder = new ProductRateTableBuilder(
            $this->createProvider(['EUR' => $eur, 'USD' => $usd]),
            new CurrencyNameResolver(),
            $this->createBasePriceProvider(100.0),
            $this->createMultiplierResolverMap([2 => 4.0, 3 => 3.0]),
            $this->createPriceFormatter(),
            $this->createLogger()
        );

        $rows = $builder->buildForProduct(10)->toArray();

        self::assertCount(1, $rows);
        self::assertSame('EUR', $rows[0]->isoCode());
        self::assertSame('Euro', $rows[0]->name());
        self::assertSame('formatted-400-EUR', $rows[0]->formattedPrice());
    }

    public function testReturnsEmptyCollectionWhenDefaultRateIsInvalid(): void
    {
        $defaultCurrency = new \Currency();
        $defaultCurrency->id = 1;
        $defaultCurrency->iso_code = 'PLN';
        $defaultCurrency->conversion_rate = 0.0;
        \Currency::$defaultCurrency = $defaultCurrency;

        $logger = $this->createLogger();
        $logger->expects(self::once())
            ->method('log')
            ->with('Product conversion table aborted: invalid default conversion rate', [
                'default_rate' => 0.0,
            ]);

        $builder = new ProductRateTableBuilder(
            $this->createProvider([]),
            new CurrencyNameResolver(),
            $this->createBasePriceProvider(100.0),
            $this->createMultiplierResolver(1.0),
            $this->createPriceFormatter(),
            $logger
        );

        self::assertSame(0, $builder->buildForProduct(10)->count());
    }

    /**
     * @param array<string, \Currency> $currencies
     */
    private function createProvider(array $currencies): CurrencyProviderInterface
    {
        return new class($currencies) implements CurrencyProviderInterface {
            /**
             * @param array<string, \Currency> $currencies
             */
            public function __construct(private array $currencies)
            {
            }

            public function getActiveCurrenciesIndexedByIsoCode(): array
            {
                return $this->currencies;
            }
        };
    }

    private function createBasePriceProvider(?float $price): ProductBasePriceProvider
    {
        \Product::$price = $price ?? 0.0;

        return new ProductBasePriceProvider($this->createLogger());
    }

    private function createMultiplierResolver(float $multiplier): CurrencyMultiplierResolver
    {
        return $this->createMultiplierResolverMap([2 => $multiplier, 3 => $multiplier]);
    }

    /**
     * @param array<int, float> $multipliersByCurrencyId
     */
    private function createMultiplierResolverMap(array $multipliersByCurrencyId): CurrencyMultiplierResolver
    {
        $cache = $this->createMock(\CurrencyRate\Application\Cache\CurrencyRatesSnapshotCacheInterface::class);
        $cache->method('getRateForCurrencyId')->willReturnCallback(
            static fn (int $currencyId): ?float => $multipliersByCurrencyId[$currencyId] ?? null
        );

        return new CurrencyMultiplierResolver($cache, $this->createLogger());
    }

    private function createPriceFormatter(): LocalizedPriceFormatter
    {
        $context = \Context::getContext();
        $context->currentLocale = new class {
            public function formatPrice(float $price, string $isoCode): string
            {
                return 'formatted-' . rtrim(rtrim(sprintf('%.2F', $price), '0'), '.') . '-' . $isoCode;
            }
        };

        return new LocalizedPriceFormatter();
    }

    private function createLogger(): DebugLoggerInterface
    {
        $logger = $this->createMock(DebugLoggerInterface::class);
        $logger->method('isEnabled')->willReturn(true);

        return $logger;
    }
}
