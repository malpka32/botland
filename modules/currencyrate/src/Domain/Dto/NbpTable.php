<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Dto;

use CurrencyRate\Domain\Collection\NbpRateCollection;

final class NbpTable
{
    public function __construct(
        private string $table,
        private string $number,
        private string $effectiveDate,
        private NbpRateCollection $rates
    ) {
    }

    public function table(): string
    {
        return $this->table;
    }

    public function number(): string
    {
        return $this->number;
    }

    public function effectiveDate(): string
    {
        return $this->effectiveDate;
    }

    public function rates(): NbpRateCollection
    {
        return $this->rates;
    }
}
