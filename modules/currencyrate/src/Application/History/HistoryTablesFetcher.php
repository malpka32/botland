<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History;

use CurrencyRate\Application\CurrencyProviderInterface;
use CurrencyRate\Application\History\Fetch\Handler\HistoryTableFetchHandlerInterface;
use CurrencyRate\Application\History\Fetch\HistoryTableFetchContext;
use CurrencyRate\Application\Support\CurrencyIsoCodeCollection;
use CurrencyRate\Domain\Collection\NbpTableCollection;

final class HistoryTablesFetcher
{
    public function __construct(
        private CurrencyProviderInterface $currencyProvider,
        private HistoryTableFetchHandlerInterface $firstHandler
    ) {
    }

    public function fetch(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): NbpTableCollection
    {
        $context = new HistoryTableFetchContext(
            $startDate,
            $endDate,
            new CurrencyIsoCodeCollection(array_keys($this->currencyProvider->getActiveCurrenciesIndexedByIsoCode()))
        );
        $this->firstHandler->handle($context);

        return $context->tables();
    }
}
