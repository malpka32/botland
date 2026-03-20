<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Dto\Shop;

final class ProductConvertedPrice
{
    public function __construct(
        private string $isoCode,
        private string $name,
        private string $symbol,
        private string $formattedPrice
    ) {
    }

    public function isoCode(): string
    {
        return $this->isoCode;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    public function formattedPrice(): string
    {
        return $this->formattedPrice;
    }
}
