<?php

declare(strict_types=1);

namespace CurrencyRate\Application\ExchangeRate;

use CurrencyRate\Application\ExchangeRate\Lookup\PlnRateLookupResolverInterface;
use CurrencyRate\Application\Support\CurrencyIsoCode;

final class NbpExchangeRateCalculator
{
    public function __construct(private PlnRateLookupResolverInterface $plnRateLookupResolver)
    {
    }

    public function resolvePlnPerCurrency(string $isoCode): float
    {
        $rate = $this->plnRateLookupResolver->resolve($isoCode);
        if ($rate === null || $rate <= 0.0) {
            return 0.0;
        }

        return $rate;
    }

    /**
     * conversion_rate in PrestaShop = target/default.
     * NBP provides PLN per currency unit:
     * target/default = (PLN/default) / (PLN/target)
     */
    public function calculatePrestaConversionRate(
        string $targetIsoCode,
        string $defaultIsoCode,
        float $plnPerDefault
    ): ?float {
        $targetIsoCode = CurrencyIsoCode::normalize($targetIsoCode);
        $defaultIsoCode = CurrencyIsoCode::normalize($defaultIsoCode);

        if ($targetIsoCode === $defaultIsoCode) {
            return 1.0;
        }

        $plnPerTarget = $this->resolvePlnPerCurrency($targetIsoCode);
        if ($plnPerTarget <= 0.0) {
            return null;
        }

        return $plnPerDefault / $plnPerTarget;
    }
}
