<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Dto\Api;

final class NbpSeriesRate
{
    public function __construct(
        private string $no,
        private string $effectiveDate,
        private float $mid
    ) {
    }

    public function no(): string
    {
        return $this->no;
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
