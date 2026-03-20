<?php

declare(strict_types=1);

namespace CurrencyRate\Application\ExchangeRate\Lookup\Step;

use CurrencyRate\Application\ExchangeRate\ExchangeRateGatewayInterface;
use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Application\Support\CurrencyIsoCode;
use CurrencyRate\Domain\Dto\Api\NbpRate;

final class NbpTableSnapshotRateLookupStep implements PlnRateLookupPipelineStepInterface
{
    /** @var array<string, float>|null */
    private ?array $ratesMap = null;

    public function __construct(
        private ExchangeRateGatewayInterface $exchangeRateGateway,
        private string $tableType,
        private DebugLoggerInterface $logger
    ) {
        $this->tableType = CurrencyIsoCode::normalize($this->tableType);
    }

    public function resolve(string $isoCode): ?float
    {
        $map = $this->getRatesMap();
        if (!isset($map[$isoCode]) || $map[$isoCode] <= 0.0) {
            return null;
        }

        $this->logger->log('PLN rate resolved by table snapshot step', [
            'table' => $this->tableType,
            'iso_code' => $isoCode,
            'mid' => $map[$isoCode],
        ]);

        return $map[$isoCode];
    }

    /**
     * @return array<string, float>
     */
    private function getRatesMap(): array
    {
        if ($this->ratesMap !== null) {
            return $this->ratesMap;
        }

        $map = [];
        try {
            $table = $this->exchangeRateGateway->fetchLatestTable($this->tableType);
            foreach ($table->rates() as $rate) {
                if (!$rate instanceof NbpRate) {
                    continue;
                }

                $map[CurrencyIsoCode::normalize($rate->code())] = $rate->mid();
            }
        } catch (\Throwable) {
        }

        $this->ratesMap = $map;

        return $this->ratesMap;
    }
}
