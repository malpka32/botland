<?php

declare(strict_types=1);

namespace CurrencyRate\Application\Product;

use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Application\Shared\CurrencyRateModuleConfig;

final class ProductBasePriceProvider
{
    public function __construct(private DebugLoggerInterface $logger)
    {
    }

    public function getBasePriceInDefaultCurrency(int $productId, ?int $productAttributeId = null): ?float
    {
        if ($productId <= 0) {
            $this->logger->log('Product base price load aborted: invalid product id', [
                'product_id' => $productId,
            ]);

            return null;
        }

        $context = \Context::getContext();
        $originalCurrency = $context->currency;
        $defaultCurrency = \Currency::getDefaultCurrency();
        if (!$defaultCurrency instanceof \Currency) {
            $this->logger->log('Product base price load aborted: no default currency');

            return null;
        }

        $context->currency = $defaultCurrency;
        $specificPriceOutput = null;

        try {
            return (float) \Product::getPriceStatic(
                $productId,
                true,
                $productAttributeId,
                CurrencyRateModuleConfig::PRICE_PRECISION,
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
    }
}
