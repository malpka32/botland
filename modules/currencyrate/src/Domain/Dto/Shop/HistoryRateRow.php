<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Dto\Shop;

final class HistoryRateRow
{
    public function __construct(
        private string $effectiveDate,
        private string $isoCode,
        private string $currencyName,
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

    public function currencyName(): string
    {
        return $this->currencyName;
    }

    public function mid(): float
    {
        return $this->mid;
    }
}
