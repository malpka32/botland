<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Dto\Persistence;

final class HistoryRateRecord
{
    public function __construct(
        private string $effectiveDate,
        private string $isoCode,
        private float $mid
    ) {
    }

    public function effectiveDate(): string
    {
        return $this->effectiveDate;
    }

    public function isoCode(): string
    {
        return $this->isoCode;
    }

    public function mid(): float
    {
        return $this->mid;
    }
}
