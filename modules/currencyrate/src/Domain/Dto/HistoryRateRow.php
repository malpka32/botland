<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Dto;

final class HistoryRateRow
{
    public function __construct(
        private string $effectiveDate,
        private string $isoCode,
        private string $currencyName,
        private float $mid
    ) {
    }

    /**
     * @return array{effective_date: string, iso_code: string, currency_name: string, mid: float}
     */
    public function toTemplateArray(): array
    {
        return [
            'effective_date' => $this->effectiveDate,
            'iso_code' => $this->isoCode,
            'currency_name' => $this->currencyName,
            'mid' => $this->mid,
        ];
    }
}
