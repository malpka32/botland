<?php

declare(strict_types=1);

namespace CurrencyRate\Application;

final class CurrencyNameResolver
{
    public function resolve(\Currency $currency): string
    {
        $langId = (int) \Context::getContext()->language->id;
        $name = $currency->name;

        if (is_array($name) && isset($name[$langId]) && is_string($name[$langId]) && $name[$langId] !== '') {
            return $name[$langId];
        }

        if (is_string($name) && $name !== '') {
            return $name;
        }

        return strtoupper((string) $currency->iso_code);
    }
}
