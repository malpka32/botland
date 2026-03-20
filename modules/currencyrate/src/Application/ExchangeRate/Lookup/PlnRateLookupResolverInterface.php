<?php

declare(strict_types=1);

namespace CurrencyRate\Application\ExchangeRate\Lookup;

interface PlnRateLookupResolverInterface
{
    public function resolve(string $isoCode): ?float;
}
