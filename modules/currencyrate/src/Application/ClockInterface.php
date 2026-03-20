<?php

declare(strict_types=1);

namespace CurrencyRate\Application;

interface ClockInterface
{
    public function now(): \DateTimeImmutable;

    public function today(): \DateTimeImmutable;
}
