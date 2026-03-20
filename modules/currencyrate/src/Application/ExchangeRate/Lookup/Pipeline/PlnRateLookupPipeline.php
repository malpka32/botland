<?php

declare(strict_types=1);

namespace CurrencyRate\Application\ExchangeRate\Lookup\Pipeline;

use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Application\ExchangeRate\Lookup\PlnRateLookupResolverInterface;
use CurrencyRate\Application\ExchangeRate\Lookup\Step\PlnRateLookupPipelineStepInterface;
use CurrencyRate\Application\Support\CurrencyIsoCode;

final class PlnRateLookupPipeline implements PlnRateLookupResolverInterface
{
    /**
     * @param iterable<PlnRateLookupPipelineStepInterface> $steps
     */
    public function __construct(
        private iterable $steps,
        private DebugLoggerInterface $logger
    ) {
    }

    public function resolve(string $isoCode): ?float
    {
        $isoCode = CurrencyIsoCode::normalize($isoCode);
        if ($isoCode === 'PLN') {
            return 1.0;
        }

        foreach ($this->steps as $step) {
            $rate = $step->resolve($isoCode);
            if ($rate !== null && $rate > 0.0) {
                return $rate;
            }
        }

        $this->logger->log('PLN rate could not be resolved by pipeline', ['iso_code' => $isoCode]);

        return null;
    }
}
