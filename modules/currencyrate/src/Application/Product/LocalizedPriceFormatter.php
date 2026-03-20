<?php

declare(strict_types=1);

namespace CurrencyRate\Application\Product;

final class LocalizedPriceFormatter
{
    public function format(float $price, string $isoCode, string $symbol): string
    {
        $context = \Context::getContext();
        if (method_exists($context, 'getCurrentLocale')) {
            $locale = $context->getCurrentLocale();
            if ($locale !== null && method_exists($locale, 'formatPrice')) {
                return (string) $locale->formatPrice($price, $isoCode);
            }
        }

        return number_format($price, 2, '.', ' ') . ' ' . $symbol;
    }
}
