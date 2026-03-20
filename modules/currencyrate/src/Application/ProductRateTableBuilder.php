<?php

declare(strict_types=1);

namespace CurrencyRate\Application;

use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Application\Product\CurrencyMultiplierResolver;
use CurrencyRate\Application\Product\LocalizedPriceFormatter;
use CurrencyRate\Application\Product\ProductBasePriceProvider;
use CurrencyRate\Application\Support\CurrencyIsoCode;
use CurrencyRate\Domain\Collection\ProductConvertedPriceCollection;
use CurrencyRate\Domain\Dto\Shop\ProductConvertedPrice;

final class ProductRateTableBuilder
{
    public function __construct(
        private CurrencyProviderInterface $shopCurrencyProvider,
        private CurrencyNameResolver $currencyNameResolver,
        private ProductBasePriceProvider $productBasePriceProvider,
        private CurrencyMultiplierResolver $currencyMultiplierResolver,
        private LocalizedPriceFormatter $priceFormatter,
        private DebugLoggerInterface $logger
    ) {
    }

    public function buildForProduct(
        int $productId,
        ?int $productAttributeId = null
    ): ProductConvertedPriceCollection
    {
        $collection = new ProductConvertedPriceCollection();
        $basePrice = $this->productBasePriceProvider->getBasePriceInDefaultCurrency($productId, $productAttributeId);
        if ($basePrice === null) {
            return $collection;
        }
        $selectedCurrencyId = $this->resolveSelectedCurrencyId();

        $defaultCurrency = \Currency::getDefaultCurrency();
        if (!$defaultCurrency instanceof \Currency) {
            $this->logger->log('Product conversion table aborted: no default currency');
            return $collection;
        }

        $activeCurrencies = $this->shopCurrencyProvider->getActiveCurrenciesIndexedByIsoCode();
        $defaultRate = (float) $defaultCurrency->conversion_rate;
        if ($defaultRate <= 0.0) {
            $this->logger->log('Product conversion table aborted: invalid default conversion rate', [
                'default_rate' => $defaultRate,
            ]);
            return $collection;
        }

        foreach ($activeCurrencies as $targetCurrency) {
            if (!$targetCurrency instanceof \Currency) {
                continue;
            }

            $targetIsoCode = CurrencyIsoCode::normalize((string) $targetCurrency->iso_code);
            $currencyId = (int) $targetCurrency->id;
            if ($currencyId <= 0) {
                continue;
            }

            if ($currencyId === $selectedCurrencyId) {
                continue;
            }

            $multiplier = $this->currencyMultiplierResolver->resolveForCurrency($targetCurrency, $defaultRate);
            if ($multiplier <= 0.0) {
                continue;
            }

            $convertedPrice = (float) $basePrice * $multiplier;
            $collection->add(
                new ProductConvertedPrice(
                    $targetIsoCode,
                    $this->currencyNameResolver->resolve($targetCurrency),
                    (string) $targetCurrency->symbol,
                    $this->priceFormatter->format(
                        $convertedPrice,
                        $targetIsoCode,
                        (string) $targetCurrency->symbol
                    )
                )
            );
        }

        return $collection;
    }

    private function resolveSelectedCurrencyId(): int
    {
        $selectedCurrency = \Context::getContext()->currency;

        return $selectedCurrency instanceof \Currency ? (int) $selectedCurrency->id : 0;
    }
}
