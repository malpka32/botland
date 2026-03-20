<?php

declare(strict_types=1);

namespace CurrencyRate\Application;

final class CurrencyDisplayNameByIsoResolver
{
    /** @var array<string, string>|null */
    private ?array $namesByIsoCode = null;

    public function __construct(
        private CurrencyProviderInterface $currencyProvider,
        private CurrencyNameResolver $currencyNameResolver
    ) {
    }

    public function resolve(string $isoCode): string
    {
        $namesByIsoCode = $this->getNamesByIsoCode();

        return $namesByIsoCode[$isoCode] ?? $isoCode;
    }

    /**
     * @return array<string, string>
     */
    private function getNamesByIsoCode(): array
    {
        if ($this->namesByIsoCode !== null) {
            return $this->namesByIsoCode;
        }

        $currenciesByIsoCode = $this->currencyProvider->getActiveCurrenciesIndexedByIsoCode();
        $namesByIsoCode = [];
        foreach ($currenciesByIsoCode as $isoCode => $currency) {
            $namesByIsoCode[$isoCode] = $this->currencyNameResolver->resolve($currency);
        }

        $this->namesByIsoCode = $namesByIsoCode;

        return $this->namesByIsoCode;
    }
}
