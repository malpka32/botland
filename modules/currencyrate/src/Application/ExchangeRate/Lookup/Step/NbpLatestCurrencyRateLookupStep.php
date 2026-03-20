<?php

declare(strict_types=1);

namespace CurrencyRate\Application\ExchangeRate\Lookup\Step;

use CurrencyRate\Application\ExchangeRate\ExchangeRateGatewayInterface;
use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Application\Support\CurrencyIsoCode;

final class NbpLatestCurrencyRateLookupStep implements PlnRateLookupPipelineStepInterface
{
    public function __construct(
        private ExchangeRateGatewayInterface $exchangeRateGateway,
        private string $tableType,
        private DebugLoggerInterface $logger
    ) {
        $this->tableType = CurrencyIsoCode::normalize($this->tableType);
    }

    public function resolve(string $isoCode): ?float
    {
        try {
            $series = $this->exchangeRateGateway->fetchLatestCurrencyRate($this->tableType, $isoCode);
        } catch (\Throwable) {
            return null;
        }

        $last = $series->rates()->last();
        if ($last === null || $last->mid() <= 0.0) {
            return null;
        }

        $this->logger->log('PLN rate resolved by latest single-currency step', [
            'table' => $this->tableType,
            'iso_code' => $isoCode,
            'mid' => $last->mid(),
        ]);

        return $last->mid();
    }
}
