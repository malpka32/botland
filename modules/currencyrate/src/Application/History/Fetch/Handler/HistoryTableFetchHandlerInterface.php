<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History\Fetch\Handler;

use CurrencyRate\Application\History\Fetch\HistoryTableFetchContext;

interface HistoryTableFetchHandlerInterface
{
    public function handle(HistoryTableFetchContext $context): void;
}
