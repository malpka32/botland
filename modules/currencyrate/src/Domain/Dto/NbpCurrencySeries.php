<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Dto;

use CurrencyRate\Domain\Collection\NbpSeriesRateCollection;

final class NbpCurrencySeries
{
    public function __construct(
        private string $table,
        private string $currency,
        private string $code,
        private NbpSeriesRateCollection $rates
    ) {
    }

    public function table(): string
    {
        return $this->table;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function code(): string
    {
        return $this->code;
    }

    public function rates(): NbpSeriesRateCollection
    {
        return $this->rates;
    }
}
