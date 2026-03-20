<?php

declare(strict_types=1);

namespace CurrencyRate\Infrastructure\Log;

final class CurrencyRateDebugLogger
{
    public const DEBUG_ENABLED_KEY = 'CURRENCYRATE_DEBUG_LOG_ENABLED';

    /**
     * @param array<string, mixed> $context
     */
    public static function log(string $message, array $context = []): void
    {
        if (!self::isEnabled()) {
            return;
        }

        $suffix = '';
        if ($context !== []) {
            $json = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            $suffix = $json !== false ? ' | context=' . $json : '';
        }

        \PrestaShopLogger::addLog('[currencyrate][debug] ' . $message . $suffix, 1);
    }

    public static function isEnabled(): bool
    {
        return (string) \Configuration::get(self::DEBUG_ENABLED_KEY) === '1';
    }
}
