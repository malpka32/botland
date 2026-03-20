<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History;

use CurrencyRate\Application\CurrencyNameResolver;
use CurrencyRate\Application\CurrencyProviderInterface;
use CurrencyRate\Domain\Collection\HistoryRateRowCollection;

final class HistoryViewProvider
{
    public function __construct(
        private RateHistoryReaderInterface $rateHistoryReader,
        private CurrencyProviderInterface $shopCurrencyProvider,
        private CurrencyNameResolver $currencyNameResolver
    ) {
    }

    public function getLastThirtyDays(
        ?string $dateFrom = null,
        ?string $dateTo = null,
        ?string $currencyIsoCode = null
    ): HistoryRateRowCollection
    {
        return $this->rateHistoryReader->findLastThirtyDays(
            $dateFrom,
            $dateTo,
            $currencyIsoCode
        );
    }

    /**
     * @return list<array{iso_code: string, name: string}>
     */
    public function getCurrencyOptions(): array
    {
        $options = [];
        $defaultCurrency = \Currency::getDefaultCurrency();
        $defaultCurrencyId = $defaultCurrency instanceof \Currency ? (int) $defaultCurrency->id : 0;
        $currencies = $this->shopCurrencyProvider->getActiveCurrenciesIndexedByIsoCode();
        foreach ($currencies as $isoCode => $currency) {
            if ($defaultCurrencyId > 0 && (int) $currency->id === $defaultCurrencyId) {
                continue;
            }

            $options[] = [
                'iso_code' => $isoCode,
                'name' => $this->currencyNameResolver->resolve($currency),
            ];
        }

        return $options;
    }
}
