<?php

declare(strict_types=1);

namespace CurrencyRate\Application\Hook;

final class DisplayHeaderHook implements HookHandlerInterface
{
    private const STYLESHEET_ID = 'module-currencyrate-product';
    private const STYLESHEET_PATH = 'modules/currencyrate/views/css/product.css';

    public function hookName(): string
    {
        return 'displayHeader';
    }

    /**
     * @param array<string, mixed> $payload
     */
    public function handle(array $payload): string
    {
        $controller = \Context::getContext()->controller ?? null;
        if (!is_object($controller)) {
            return '';
        }

        $phpSelf = isset($controller->php_self) ? (string) $controller->php_self : '';
        if ($phpSelf !== 'product') {
            return '';
        }

        $controller->registerStylesheet(
            self::STYLESHEET_ID,
            self::STYLESHEET_PATH,
            ['media' => 'all', 'priority' => 90]
        );

        return '';
    }
}
