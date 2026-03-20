<?php

declare(strict_types=1);

namespace CurrencyRate\Application;

interface CurrencyProviderInterface
{
    /**
     * @return array<string, \Currency>
     */
    public function getActiveCurrenciesIndexedByIsoCode(): array;
}
