<?php

declare(strict_types=1);

namespace CurrencyRate\Application\ExchangeRate;

interface PlnRateLookupStrategyInterface
{
    public function resolve(string $isoCode): ?float;
}
