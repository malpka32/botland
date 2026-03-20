<?php

declare(strict_types=1);

namespace CurrencyRate\Application\ExchangeRate;

use CurrencyRate\Application\CurrencyProviderInterface;

final class CurrencyRateUpdater
{
    public function __construct(
        private CurrencyProviderInterface $shopCurrencyProvider,
        private NbpExchangeRateCalculator $nbpExchangeRateCalculator
    ) {
    }

    public function refreshCurrentRates(): void
    {
        $defaultCurrency = \Currency::getDefaultCurrency();
        if (!$defaultCurrency instanceof \Currency) {
            throw new \RuntimeException('Default currency is not configured.');
        }

        $plnPerDefault = $this->nbpExchangeRateCalculator->resolvePlnPerCurrency($defaultCurrency->iso_code);
        if ($plnPerDefault <= 0.0) {
            throw new \RuntimeException(sprintf('Cannot resolve PLN rate for default currency "%s".', $defaultCurrency->iso_code));
        }

        foreach ($this->shopCurrencyProvider->getActiveCurrenciesIndexedByIsoCode() as $isoCode => $currency) {
            $newRate = $this->nbpExchangeRateCalculator->calculatePrestaConversionRate(
                $isoCode,
                $defaultCurrency->iso_code,
                $plnPerDefault
            );
            if ($newRate === null) {
                continue;
            }

            if (abs((float) $currency->conversion_rate - $newRate) > 0.000001) {
                $currency->conversion_rate = round($newRate, 6);
                $currency->update();
            }
        }
    }
}
