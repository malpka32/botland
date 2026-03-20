<?php

declare(strict_types=1);

namespace CurrencyRate\Application\Hook;

interface HookHandlerInterface
{
    public function hookName(): string;

    /**
     * @param array<string, mixed> $payload
     *
     * @return mixed
     */
    public function handle(array $payload);
}
