<?php

declare(strict_types=1);

namespace CurrencyRate\Application\ExchangeRate\Lookup\Step;

interface PlnRateLookupPipelineStepInterface
{
    public function resolve(string $isoCode): ?float;
}
