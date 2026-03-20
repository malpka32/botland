<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application\History;

use CurrencyRate\Application\History\HistoryDateRange;
use CurrencyRate\Application\History\HistoryRateRepositoryInterface;
use CurrencyRate\Application\History\HistoryRateRowCollectionMapper;
use CurrencyRate\Application\History\RateHistoryReader;
use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Domain\Collection\HistoryRateRecordCollection;
use CurrencyRate\Domain\Collection\HistoryRateRowCollection;
use PHPUnit\Framework\TestCase;

final class RateHistoryReaderTest extends TestCase
{
    public function testFindLastThirtyDaysNormalizesCurrencyAndMapsRecords(): void
    {
        $records = new HistoryRateRecordCollection();
        $mappedCollection = new HistoryRateRowCollection();
        $minimumEffectiveDate = HistoryDateRange::defaultStartDateFrom(HistoryDateRange::today())->format('Y-m-d');

        $repository = $this->createMock(HistoryRateRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findRows')
            ->with($minimumEffectiveDate, '2026-03-01', '2026-03-20', 'EUR')
            ->willReturn($records);

        $mapper = $this->createMock(HistoryRateRowCollectionMapper::class);
        $mapper->expects(self::once())
            ->method('map')
            ->with($records)
            ->willReturn($mappedCollection);

        $logger = $this->createMock(DebugLoggerInterface::class);
        $logger->expects(self::exactly(2))->method('log');

        $reader = new RateHistoryReader($mapper, $logger, $repository);

        self::assertSame(
            $mappedCollection,
            $reader->findLastThirtyDays('2026-03-01', '2026-03-20', ' eur ')
        );
    }

    public function testFindLastThirtyDaysPassesNullWhenNormalizedCurrencyIsEmpty(): void
    {
        $records = new HistoryRateRecordCollection();
        $mappedCollection = new HistoryRateRowCollection();
        $minimumEffectiveDate = HistoryDateRange::defaultStartDateFrom(HistoryDateRange::today())->format('Y-m-d');

        $repository = $this->createMock(HistoryRateRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findRows')
            ->with($minimumEffectiveDate, null, null, null)
            ->willReturn($records);

        $mapper = $this->createMock(HistoryRateRowCollectionMapper::class);
        $mapper->expects(self::once())
            ->method('map')
            ->with($records)
            ->willReturn($mappedCollection);

        $logger = $this->createMock(DebugLoggerInterface::class);
        $logger->expects(self::exactly(2))->method('log');

        $reader = new RateHistoryReader($mapper, $logger, $repository);

        self::assertSame($mappedCollection, $reader->findLastThirtyDays(null, null, '   '));
    }
}
