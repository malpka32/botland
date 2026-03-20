<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History\Fetch\Handler;

use CurrencyRate\Application\ExchangeRate\ExchangeRateGatewayInterface;
use CurrencyRate\Application\History\Fetch\HistoryTableFetchContext;
use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Application\Support\CurrencyIsoCodeCollection;

final class FetchTableARangeHandler implements HistoryTableFetchHandlerInterface
{
    public function __construct(
        private ExchangeRateGatewayInterface $gateway,
        private DebugLoggerInterface $logger,
        private ?HistoryTableFetchHandlerInterface $next = null
    ) {
    }

    public function handle(HistoryTableFetchContext $context): void
    {
        $tablesA = $this->gateway->fetchTableRange('A', $context->startDate(), $context->endDate());
        $context->addTables($tablesA);

        $coveredByA = CurrencyIsoCodeCollection::fromNbpTables($tablesA, 'A');
        $missingIsoCodes = $context->activeIsoCodes()->missingFromCollection($coveredByA);

        if ($missingIsoCodes === []) {
            $this->logger->log('History fetch skipped table B range; table A already covers active currencies');

            return;
        }

        $this->logger->log('History fetch continues to table B range for missing currencies', [
            'missing_iso_codes' => $missingIsoCodes,
        ]);

        $this->next?->handle($context);
    }
}
