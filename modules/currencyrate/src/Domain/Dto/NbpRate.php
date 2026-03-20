<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Dto;

final class NbpRate
{
    public function __construct(
        private string $currency,
        private string $code,
        private float $mid
    ) {
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function mid(): float
    {
        return $this->mid;
    }
}
