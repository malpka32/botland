<?php

declare(strict_types=1);

namespace CurrencyRate\Application\ExchangeRate;

use CurrencyRate\Application\Cache\CurrencyRatesSnapshotCacheInterface;
use CurrencyRate\Application\CurrencyProviderInterface;
use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Application\Shared\CurrencyRateModuleConfig;
use CurrencyRate\Application\Support\CurrencyIsoCode;

final class CurrencyRateUpdater
{
    public function __construct(
        private CurrencyProviderInterface $shopCurrencyProvider,
        private NbpExchangeRateCalculator $nbpExchangeRateCalculator,
        private CurrencyRatesSnapshotCacheInterface $currencyRatesSnapshotCache,
        private DebugLoggerInterface $logger
    ) {
    }

    public function refreshCurrentRates(): void
    {
        $this->logger->log('Currency rates refresh started');
        $defaultCurrency = \Currency::getDefaultCurrency();
        if (!$defaultCurrency instanceof \Currency) {
            throw new \RuntimeException('Default currency is not configured.');
        }

        $plnPerDefault = $this->nbpExchangeRateCalculator->resolvePlnPerCurrency(
            CurrencyIsoCode::normalize((string) $defaultCurrency->iso_code)
        );
        if ($plnPerDefault <= 0.0) {
            throw new \RuntimeException(sprintf('Cannot resolve PLN rate for default currency "%s".', $defaultCurrency->iso_code));
        }

        foreach ($this->shopCurrencyProvider->getActiveCurrenciesIndexedByIsoCode() as $isoCode => $currency) {
            $newRate = $this->nbpExchangeRateCalculator->calculatePrestaConversionRate(
                $isoCode,
                    CurrencyIsoCode::normalize((string) $defaultCurrency->iso_code),
                $plnPerDefault
            );
            if ($newRate === null) {
                continue;
            }

            if (abs((float) $currency->conversion_rate - $newRate) > CurrencyRateModuleConfig::RATE_UPDATE_EPSILON) {
                $currency->conversion_rate = round($newRate, 6);
                $currency->update();
            }
        }

        $this->currencyRatesSnapshotCache->invalidateAll();
        $this->logger->log('Currency rates refresh finished');
    }
}
