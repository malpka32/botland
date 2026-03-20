<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application\History;

use CurrencyRate\Application\CurrencyDisplayNameByIsoResolver;
use CurrencyRate\Application\CurrencyNameResolver;
use CurrencyRate\Application\CurrencyProviderInterface;
use CurrencyRate\Application\History\HistoryDateRange;
use CurrencyRate\Application\History\HistoryRateRepositoryInterface;
use CurrencyRate\Application\History\HistoryRateRowCollectionMapper;
use CurrencyRate\Application\History\RateHistoryReader;
use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Domain\Collection\HistoryRateRecordCollection;
use CurrencyRate\Domain\Collection\HistoryRateRowCollection;
use CurrencyRate\Domain\Dto\Persistence\HistoryRateRecord;
use PHPUnit\Framework\TestCase;

final class RateHistoryReaderTest extends TestCase
{
    public function testFindLastThirtyDaysNormalizesCurrencyAndMapsRecords(): void
    {
        $records = new HistoryRateRecordCollection();
        $records->add(new HistoryRateRecord('2026-03-20', 'EUR', 4.2));
        $minimumEffectiveDate = HistoryDateRange::defaultStartDateFrom(HistoryDateRange::today())->format('Y-m-d');

        $repository = $this->createMock(HistoryRateRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findRows')
            ->with($minimumEffectiveDate, '2026-03-01', '2026-03-20', 'EUR')
            ->willReturn($records);

        $logger = $this->createMock(DebugLoggerInterface::class);
        $logger->expects(self::exactly(2))->method('log');

        $mapper = new HistoryRateRowCollectionMapper($this->createResolver());
        $reader = new RateHistoryReader($mapper, $logger, $repository);
        $result = $reader->findLastThirtyDays('2026-03-01', '2026-03-20', ' eur ');

        self::assertInstanceOf(HistoryRateRowCollection::class, $result);
        self::assertCount(1, $result->toArray());
        self::assertSame('EUR', $result->toArray()[0]->isoCode());
    }

    public function testFindLastThirtyDaysPassesNullWhenNormalizedCurrencyIsEmpty(): void
    {
        $records = new HistoryRateRecordCollection();
        $minimumEffectiveDate = HistoryDateRange::defaultStartDateFrom(HistoryDateRange::today())->format('Y-m-d');

        $repository = $this->createMock(HistoryRateRepositoryInterface::class);
        $repository->expects(self::once())
            ->method('findRows')
            ->with($minimumEffectiveDate, null, null, null)
            ->willReturn($records);

        $logger = $this->createMock(DebugLoggerInterface::class);
        $logger->expects(self::exactly(2))->method('log');

        $mapper = new HistoryRateRowCollectionMapper($this->createResolver());
        $reader = new RateHistoryReader($mapper, $logger, $repository);

        self::assertCount(0, $reader->findLastThirtyDays(null, null, '   ')->toArray());
    }

    private function createResolver(): CurrencyDisplayNameByIsoResolver
    {
        return new CurrencyDisplayNameByIsoResolver(
            new class implements CurrencyProviderInterface {
                public function getActiveCurrenciesIndexedByIsoCode(): array
                {
                    return [];
                }
            },
            new CurrencyNameResolver()
        );
    }
}
