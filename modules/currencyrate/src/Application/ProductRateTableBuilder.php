<?php

declare(strict_types=1);

namespace CurrencyRate\Application;

use CurrencyRate\Domain\Collection\ProductConvertedPriceCollection;
use CurrencyRate\Domain\Dto\ProductConvertedPrice;

final class ProductRateTableBuilder
{
    public function __construct(
        private CurrencyProviderInterface $shopCurrencyProvider,
        private CurrencyNameResolver $currencyNameResolver
    ) {
    }

    public function buildForProduct(
        int $productId,
        ?int $productAttributeId = null
    ): ProductConvertedPriceCollection
    {
        $collection = new ProductConvertedPriceCollection();
        if ($productId <= 0) {
            return $collection;
        }

        $context = \Context::getContext();
        $defaultCurrency = \Currency::getDefaultCurrency();
        if (!$defaultCurrency instanceof \Currency) {
            return $collection;
        }

        $originalCurrency = $context->currency;
        $context->currency = $defaultCurrency;
        $specificPriceOutput = null;

        try {
            $basePrice = \Product::getPriceStatic(
                $productId,
                true,
                $productAttributeId,
                6,
                null,
                false,
                true,
                1,
                false,
                null,
                null,
                null,
                $specificPriceOutput,
                true,
                true,
                $context
            );
        } finally {
            $context->currency = $originalCurrency;
        }

        foreach ($this->shopCurrencyProvider->getActiveCurrenciesIndexedByIsoCode() as $currency) {
            if (!$currency instanceof \Currency) {
                continue;
            }

            if ($originalCurrency instanceof \Currency && (int) $currency->id === (int) $originalCurrency->id) {
                continue;
            }

            $convertedPrice = \Tools::convertPriceFull((float) $basePrice, $defaultCurrency, $currency);
            $collection->add(
                new ProductConvertedPrice(
                    strtoupper((string) $currency->iso_code),
                    $this->currencyNameResolver->resolve($currency),
                    (string) $currency->symbol,
                    $this->formatPrice($convertedPrice, $currency)
                )
            );
        }

        return $collection;
    }
    private function formatPrice(float $price, \Currency $currency): string
    {
        $context = \Context::getContext();
        if (method_exists($context, 'getCurrentLocale')) {
            $locale = $context->getCurrentLocale();
            if ($locale !== null && method_exists($locale, 'formatPrice')) {
                return (string) $locale->formatPrice($price, (string) $currency->iso_code);
            }
        }

        return number_format($price, 2, '.', ' ') . ' ' . (string) $currency->symbol;
    }
}
