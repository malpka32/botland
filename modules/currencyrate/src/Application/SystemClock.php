<?php

declare(strict_types=1);

namespace CurrencyRate\Application;

final class SystemClock implements ClockInterface
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now');
    }

    public function today(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('today');
    }
}
