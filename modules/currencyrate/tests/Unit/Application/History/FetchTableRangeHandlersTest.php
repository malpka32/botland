<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application\History;

use CurrencyRate\Application\ExchangeRate\ExchangeRateGatewayInterface;
use CurrencyRate\Application\History\Fetch\Handler\FetchTableARangeHandler;
use CurrencyRate\Application\History\Fetch\Handler\FetchTableBRangeHandler;
use CurrencyRate\Application\History\Fetch\Handler\HistoryTableFetchHandlerInterface;
use CurrencyRate\Application\History\Fetch\HistoryTableFetchContext;
use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Application\Support\CurrencyIsoCodeCollection;
use CurrencyRate\Domain\Collection\NbpRateCollection;
use CurrencyRate\Domain\Collection\NbpTableCollection;
use CurrencyRate\Domain\Dto\Api\NbpRate;
use CurrencyRate\Domain\Dto\Api\NbpTable;
use PHPUnit\Framework\TestCase;

final class FetchTableRangeHandlersTest extends TestCase
{
    public function testFetchTableARangeHandlerStopsWhenTableACoversAllCurrencies(): void
    {
        $gateway = $this->createMock(ExchangeRateGatewayInterface::class);
        $gateway->expects(self::once())
            ->method('fetchTableRange')
            ->with('A', self::isInstanceOf(\DateTimeImmutable::class), self::isInstanceOf(\DateTimeImmutable::class))
            ->willReturn($this->buildCollection($this->buildTable('A', ['EUR' => 4.2, 'USD' => 3.9])));

        $next = $this->createMock(HistoryTableFetchHandlerInterface::class);
        $next->expects(self::never())->method('handle');

        $logger = $this->createMock(DebugLoggerInterface::class);
        $logger->expects(self::once())
            ->method('log')
            ->with('History fetch skipped table B range; table A already covers active currencies');

        $handler = new FetchTableARangeHandler($gateway, $logger, $next);
        $context = new HistoryTableFetchContext(
            new \DateTimeImmutable('2026-03-01'),
            new \DateTimeImmutable('2026-03-20'),
            new CurrencyIsoCodeCollection(['EUR', 'USD'])
        );

        $handler->handle($context);

        self::assertSame(1, $context->tables()->count());
    }

    public function testFetchTableARangeHandlerDelegatesWhenCurrenciesAreMissing(): void
    {
        $gateway = $this->createMock(ExchangeRateGatewayInterface::class);
        $gateway->expects(self::once())
            ->method('fetchTableRange')
            ->with('A', self::isInstanceOf(\DateTimeImmutable::class), self::isInstanceOf(\DateTimeImmutable::class))
            ->willReturn($this->buildCollection($this->buildTable('A', ['EUR' => 4.2])));

        $next = $this->createMock(HistoryTableFetchHandlerInterface::class);
        $next->expects(self::once())->method('handle');

        $logger = $this->createMock(DebugLoggerInterface::class);
        $logger->expects(self::once())
            ->method('log')
            ->with(
                'History fetch continues to table B range for missing currencies',
                ['missing_iso_codes' => ['USD']]
            );

        $handler = new FetchTableARangeHandler($gateway, $logger, $next);
        $context = new HistoryTableFetchContext(
            new \DateTimeImmutable('2026-03-01'),
            new \DateTimeImmutable('2026-03-20'),
            new CurrencyIsoCodeCollection(['EUR', 'USD'])
        );

        $handler->handle($context);

        self::assertSame(1, $context->tables()->count());
    }

    public function testFetchTableBRangeHandlerAddsTableBCollection(): void
    {
        $gateway = $this->createMock(ExchangeRateGatewayInterface::class);
        $gateway->expects(self::once())
            ->method('fetchTableRange')
            ->with('B', self::isInstanceOf(\DateTimeImmutable::class), self::isInstanceOf(\DateTimeImmutable::class))
            ->willReturn($this->buildCollection($this->buildTable('B', ['CHF' => 4.5])));

        $handler = new FetchTableBRangeHandler($gateway);
        $context = new HistoryTableFetchContext(
            new \DateTimeImmutable('2026-03-01'),
            new \DateTimeImmutable('2026-03-20'),
            new CurrencyIsoCodeCollection(['CHF'])
        );

        $handler->handle($context);

        self::assertSame(1, $context->tables()->count());
    }

    private function buildCollection(NbpTable $table): NbpTableCollection
    {
        $collection = new NbpTableCollection();
        $collection->add($table);

        return $collection;
    }

    /**
     * @param array<string, float> $ratesByIso
     */
    private function buildTable(string $tableType, array $ratesByIso): NbpTable
    {
        $rates = new NbpRateCollection();
        foreach ($ratesByIso as $isoCode => $mid) {
            $rates->add(new NbpRate($isoCode, $isoCode, $mid));
        }

        return new NbpTable($tableType, '1/' . $tableType . '/NBP/2026', '2026-03-20', $rates);
    }
}
