<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Dto;

final class ProductConvertedPrice
{
    public function __construct(
        private string $isoCode,
        private string $name,
        private string $symbol,
        private string $formattedPrice
    ) {
    }

    /**
     * @return array{iso_code: string, name: string, symbol: string, formatted_price: string}
     */
    public function toTemplateArray(): array
    {
        return [
            'iso_code' => $this->isoCode,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'formatted_price' => $this->formattedPrice,
        ];
    }
}
