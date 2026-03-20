<?php

declare(strict_types=1);

namespace CurrencyRate\Application\ExchangeRate;

final class NbpExchangeRateCalculator
{
    public function __construct(private PlnRateLookupStrategyInterface $plnRateLookupStrategy)
    {
    }

    public function resolvePlnPerCurrency(string $isoCode): float
    {
        $rate = $this->plnRateLookupStrategy->resolve($isoCode);
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
        $targetIsoCode = strtoupper($targetIsoCode);
        $defaultIsoCode = strtoupper($defaultIsoCode);

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
