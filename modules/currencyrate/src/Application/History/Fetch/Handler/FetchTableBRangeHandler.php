<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History\Fetch\Handler;

use CurrencyRate\Application\ExchangeRate\ExchangeRateGatewayInterface;
use CurrencyRate\Application\History\Fetch\HistoryTableFetchContext;

final class FetchTableBRangeHandler implements HistoryTableFetchHandlerInterface
{
    public function __construct(private ExchangeRateGatewayInterface $gateway)
    {
    }

    public function handle(HistoryTableFetchContext $context): void
    {
        $context->addTables($this->gateway->fetchTableRange('B', $context->startDate(), $context->endDate()));
    }
}
