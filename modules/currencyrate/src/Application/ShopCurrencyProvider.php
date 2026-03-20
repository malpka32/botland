<?php

declare(strict_types=1);

namespace CurrencyRate\Application;

final class ShopCurrencyProvider implements CurrencyProviderInterface
{
    /**
     * @return array<string, \Currency>
     */
    public function getActiveCurrenciesIndexedByIsoCode(): array
    {
        $result = [];
        $currencies = \Currency::getCurrencies(true, false, true);

        foreach ($currencies as $currency) {
            if (!$currency instanceof \Currency) {
                continue;
            }

            $iso = strtoupper((string) $currency->iso_code);
            if ($iso === '') {
                continue;
            }

            $result[$iso] = $currency;
        }

        return $result;
    }
}
