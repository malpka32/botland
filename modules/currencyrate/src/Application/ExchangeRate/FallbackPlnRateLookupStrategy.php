<?php

declare(strict_types=1);

namespace CurrencyRate\Application\ExchangeRate;

use CurrencyRate\Infrastructure\Log\CurrencyRateDebugLogger;

final class FallbackPlnRateLookupStrategy implements PlnRateLookupStrategyInterface
{
    public function __construct(
        private PlnRateLookupStrategyInterface $primaryStrategy,
        private PlnRateLookupStrategyInterface $fallbackStrategy
    ) {
    }

    public function resolve(string $isoCode): ?float
    {
        $primary = $this->primaryStrategy->resolve($isoCode);
        if ($primary !== null && $primary > 0.0) {
            CurrencyRateDebugLogger::log('PLN rate resolved by primary strategy', ['iso_code' => strtoupper($isoCode)]);
            return $primary;
        }

        CurrencyRateDebugLogger::log('PLN rate fallback strategy invoked', ['iso_code' => strtoupper($isoCode)]);
        $fallback = $this->fallbackStrategy->resolve($isoCode);
        if ($fallback !== null && $fallback > 0.0) {
            CurrencyRateDebugLogger::log('PLN rate resolved by fallback strategy', ['iso_code' => strtoupper($isoCode)]);
        }

        return $fallback;
    }
}
