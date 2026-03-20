<?php

declare(strict_types=1);

namespace CurrencyRate\Application\ExchangeRate;

use CurrencyRate\Domain\Dto\NbpRate;
use CurrencyRate\Infrastructure\Log\CurrencyRateDebugLogger;

final class NbpTablePlnRateLookupStrategy implements PlnRateLookupStrategyInterface
{
    /** @var array<string, float>|null */
    private ?array $plnRateMap = null;

    public function __construct(
        private ExchangeRateGatewayInterface $exchangeRateGateway,
        private string $tableType
    ) {
    }

    public function resolve(string $isoCode): ?float
    {
        $isoCode = strtoupper($isoCode);
        if ($isoCode === 'PLN') {
            return 1.0;
        }

        $map = $this->getPlnRateMap();
        if (isset($map[$isoCode]) && $map[$isoCode] > 0.0) {
            CurrencyRateDebugLogger::log('PLN rate resolved from table map', [
                'table' => strtoupper($this->tableType),
                'iso_code' => $isoCode,
            ]);
            return $map[$isoCode];
        }

        try {
            CurrencyRateDebugLogger::log('PLN rate lookup fallback to currency series', [
                'table' => strtoupper($this->tableType),
                'iso_code' => $isoCode,
            ]);
            $series = $this->exchangeRateGateway->fetchCurrencySeries($this->tableType, $isoCode, 1);
        } catch (\Throwable $exception) {
            CurrencyRateDebugLogger::log('PLN rate lookup failed on currency series', [
                'table' => strtoupper($this->tableType),
                'iso_code' => $isoCode,
            ]);
            return null;
        }

        $last = $series->rates()->last();
        if ($last === null || $last->mid() <= 0.0) {
            CurrencyRateDebugLogger::log('PLN rate missing in series payload', [
                'table' => strtoupper($this->tableType),
                'iso_code' => $isoCode,
            ]);
            return null;
        }

        CurrencyRateDebugLogger::log('PLN rate resolved from currency series', [
            'table' => strtoupper($this->tableType),
            'iso_code' => $isoCode,
            'mid' => $last->mid(),
        ]);
        return $last->mid();
    }

    /**
     * @return array<string, float>
     */
    private function getPlnRateMap(): array
    {
        if ($this->plnRateMap !== null) {
            return $this->plnRateMap;
        }

        $map = ['PLN' => 1.0];
        try {
            $table = $this->exchangeRateGateway->fetchLatestTable($this->tableType);
            foreach ($table->rates() as $rate) {
                if (!$rate instanceof NbpRate) {
                    continue;
                }

                $map[strtoupper($rate->code())] = $rate->mid();
            }
            CurrencyRateDebugLogger::log('PLN rate map prepared from table', [
                'table' => strtoupper($this->tableType),
                'rates_count' => count($map),
            ]);
        } catch (\Throwable $exception) {
            // fallback to per-currency endpoint in resolve()
            CurrencyRateDebugLogger::log('PLN rate map load failed for table', [
                'table' => strtoupper($this->tableType),
            ]);
        }

        $this->plnRateMap = $map;

        return $this->plnRateMap;
    }
}
