<?php

declare(strict_types=1);

namespace CurrencyRate\Application\Support;

final class CurrencyIsoCode
{
    public static function normalize(string $isoCode): string
    {
        return strtoupper(trim($isoCode));
    }
}
