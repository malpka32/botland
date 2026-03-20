<?php

declare(strict_types=1);

namespace CurrencyRate\Application\Product;

final class LocalizedPriceFormatter
{
    public function format(float $price, string $isoCode, string $symbol): string
    {
        $locale = $this->resolveLocale();
        if ($locale !== null && method_exists($locale, 'formatPrice')) {
            return (string) $locale->formatPrice($price, $isoCode);
        }

        return number_format($price, 2, '.', ' ') . ' ' . $symbol;
    }

    private function resolveLocale(): mixed
    {
        $context = \Context::getContext();

        return method_exists($context, 'getCurrentLocale') ? $context->getCurrentLocale() : null;
    }
}
