<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application\ExchangeRate\Lookup;

use CurrencyRate\Application\ExchangeRate\ExchangeRateGatewayInterface;
use CurrencyRate\Application\ExchangeRate\Lookup\Step\NbpLatestCurrencyRateLookupStep;
use CurrencyRate\Application\ExchangeRate\Lookup\Step\NbpTableSnapshotRateLookupStep;
use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Domain\Collection\NbpRateCollection;
use CurrencyRate\Domain\Collection\NbpSeriesRateCollection;
use CurrencyRate\Domain\Dto\Api\NbpCurrencySeries;
use CurrencyRate\Domain\Dto\Api\NbpRate;
use CurrencyRate\Domain\Dto\Api\NbpSeriesRate;
use CurrencyRate\Domain\Dto\Api\NbpTable;
use PHPUnit\Framework\TestCase;

final class NbpLookupStepsTest extends TestCase
{
    public function testLatestCurrencyRateStepReturnsRate(): void
    {
        $gateway = $this->createMock(ExchangeRateGatewayInterface::class);
        $gateway->method('fetchLatestCurrencyRate')
            ->willReturn($this->buildSeries(4.3));

        $logger = $this->createLogger();
        $logger->expects(self::once())->method('log');

        $step = new NbpLatestCurrencyRateLookupStep($gateway, 'a', $logger);

        self::assertSame(4.3, $step->resolve('EUR'));
    }

    public function testLatestCurrencyRateStepReturnsNullOnGatewayException(): void
    {
        $gateway = $this->createMock(ExchangeRateGatewayInterface::class);
        $gateway->method('fetchLatestCurrencyRate')->willThrowException(new \RuntimeException('boom'));

        $logger = $this->createLogger();
        $logger->expects(self::never())->method('log');

        $step = new NbpLatestCurrencyRateLookupStep($gateway, 'a', $logger);

        self::assertNull($step->resolve('EUR'));
    }

    public function testTableSnapshotStepResolvesRateAndCachesTableMap(): void
    {
        $gateway = $this->createMock(ExchangeRateGatewayInterface::class);
        $gateway->expects(self::once())
            ->method('fetchLatestTable')
            ->willReturn($this->buildTable(['EUR' => 4.2, 'USD' => 3.9]));

        $logger = $this->createLogger();
        $logger->expects(self::exactly(2))->method('log');

        $step = new NbpTableSnapshotRateLookupStep($gateway, 'a', $logger);

        self::assertSame(4.2, $step->resolve('EUR'));
        self::assertSame(3.9, $step->resolve('USD'));
        self::assertNull($step->resolve('CHF'));
    }

    private function buildSeries(float $mid): NbpCurrencySeries
    {
        $rates = new NbpSeriesRateCollection();
        $rates->add(new NbpSeriesRate('1/A/NBP/2026', '2026-03-20', $mid));

        return new NbpCurrencySeries('A', 'Euro', 'EUR', $rates);
    }

    /**
     * @param array<string, float> $ratesByIso
     */
    private function buildTable(array $ratesByIso): NbpTable
    {
        $rates = new NbpRateCollection();
        foreach ($ratesByIso as $iso => $mid) {
            $rates->add(new NbpRate($iso, $iso, $mid));
        }

        return new NbpTable('A', '1/A/NBP/2026', '2026-03-20', $rates);
    }

    private function createLogger(): DebugLoggerInterface
    {
        $logger = $this->createMock(DebugLoggerInterface::class);
        $logger->method('isEnabled')->willReturn(true);

        return $logger;
    }
}
