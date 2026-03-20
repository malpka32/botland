<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Dto;

final class NbpSeriesRate
{
    public function __construct(
        private string $number,
        private string $effectiveDate,
        private float $mid
    ) {
    }

    public function number(): string
    {
        return $this->number;
    }

    public function effectiveDate(): string
    {
        return $this->effectiveDate;
    }

    public function mid(): float
    {
        return $this->mid;
    }
}
