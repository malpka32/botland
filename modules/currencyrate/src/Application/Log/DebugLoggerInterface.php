<?php

declare(strict_types=1);

namespace CurrencyRate\Application\Log;

interface DebugLoggerInterface
{
    /**
     * @param array<string, mixed> $context
     */
    public function log(string $message, array $context = []): void;

    public function isEnabled(): bool;
}
