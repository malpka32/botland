<?php

declare(strict_types=1);

namespace CurrencyRate\Infrastructure\CircuitBreaker;

use PrestaShop\CircuitBreaker\Contract\CircuitBreakerInterface;

final class PassThroughCircuitBreaker implements CircuitBreakerInterface
{
    public function getState(): string
    {
        return 'closed';
    }

    public function call(string $service, array $parameters = [], callable $fallback = null): string
    {
        $context = stream_context_create($parameters);
        $response = @file_get_contents($service, false, $context);
        if ($response !== false) {
            return $response;
        }

        if ($fallback !== null) {
            return (string) $fallback();
        }

        return '';
    }

    public function isOpened(): bool
    {
        return false;
    }

    public function isHalfOpened(): bool
    {
        return false;
    }

    public function isClosed(): bool
    {
        return true;
    }
}
