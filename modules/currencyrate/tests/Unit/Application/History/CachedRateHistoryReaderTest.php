<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application\History;

use CurrencyRate\Application\Cache\RateHistoryCacheInterface;
use CurrencyRate\Application\History\CachedRateHistoryReader;
use CurrencyRate\Application\History\RateHistoryReaderInterface;
use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Domain\Collection\HistoryRateRowCollection;
use PHPUnit\Framework\TestCase;

final class CachedRateHistoryReaderTest extends TestCase
{
    protected function setUp(): void
    {
        $context = \Context::getContext();
        $context->shop = null;
        $context->language = (object) ['id' => 1];
    }

    public function testBypassesCacheWhenContextIdentifiersAreMissing(): void
    {
        $innerReader = $this->createMock(RateHistoryReaderInterface::class);
        $expected = new HistoryRateRowCollection();
        $innerReader->expects(self::once())
            ->method('findLastThirtyDays')
            ->with('2026-03-01', '2026-03-20', 'EUR')
            ->willReturn($expected);

        $cache = $this->createMock(RateHistoryCacheInterface::class);
        $cache->expects(self::never())->method('getForContext');

        $logger = $this->createMock(DebugLoggerInterface::class);
        $logger->expects(self::once())->method('log');

        $reader = new CachedRateHistoryReader($innerReader, $cache, $logger);

        self::assertSame($expected, $reader->findLastThirtyDays('2026-03-01', '2026-03-20', 'EUR'));
    }

    public function testReturnsCachedCollectionWhenCacheHitOccurs(): void
    {
        $context = \Context::getContext();
        $context->shop = (object) ['id' => 2];
        $context->language = (object) ['id' => 3];

        $cachedCollection = new HistoryRateRowCollection();

        $innerReader = $this->createMock(RateHistoryReaderInterface::class);
        $innerReader->expects(self::never())->method('findLastThirtyDays');

        $cache = $this->createMock(RateHistoryCacheInterface::class);
        $cache->expects(self::once())
            ->method('getForContext')
            ->with(2, 3, null, null, null)
            ->willReturn($cachedCollection);
        $cache->expects(self::never())->method('storeForContext');

        $logger = $this->createMock(DebugLoggerInterface::class);
        $logger->expects(self::once())
            ->method('log')
            ->with('History cache hit', [
                'shop_id' => 2,
                'language_id' => 3,
                'date_from' => null,
                'date_to' => null,
                'currency' => null,
            ]);

        $reader = new CachedRateHistoryReader($innerReader, $cache, $logger);

        self::assertSame($cachedCollection, $reader->findLastThirtyDays());
    }

    public function testStoresCollectionWhenCacheMissOccurs(): void
    {
        $context = \Context::getContext();
        $context->shop = (object) ['id' => 4];
        $context->language = (object) ['id' => 5];

        $loadedCollection = new HistoryRateRowCollection();

        $innerReader = $this->createMock(RateHistoryReaderInterface::class);
        $innerReader->expects(self::once())
            ->method('findLastThirtyDays')
            ->with('2026-03-01', '2026-03-20', 'USD')
            ->willReturn($loadedCollection);

        $cache = $this->createMock(RateHistoryCacheInterface::class);
        $cache->expects(self::once())
            ->method('getForContext')
            ->with(4, 5, '2026-03-01', '2026-03-20', 'USD')
            ->willReturn(null);
        $cache->expects(self::once())
            ->method('storeForContext')
            ->with(4, 5, '2026-03-01', '2026-03-20', 'USD', $loadedCollection);

        $loggedMessages = [];
        $logger = $this->createMock(DebugLoggerInterface::class);
        $logger->expects(self::exactly(2))
            ->method('log')
            ->willReturnCallback(static function (string $message, array $context = []) use (&$loggedMessages): void {
                $loggedMessages[] = [$message, $context];
            });

        $reader = new CachedRateHistoryReader($innerReader, $cache, $logger);

        self::assertSame(
            $loadedCollection,
            $reader->findLastThirtyDays('2026-03-01', '2026-03-20', 'USD')
        );
        self::assertSame([
            [
                'History cache miss',
                [
                    'shop_id' => 4,
                    'language_id' => 5,
                    'date_from' => '2026-03-01',
                    'date_to' => '2026-03-20',
                    'currency' => 'USD',
                ],
            ],
            [
                'History cache stored',
                [
                    'shop_id' => 4,
                    'language_id' => 5,
                    'rows_count' => 0,
                    'date_from' => '2026-03-01',
                    'date_to' => '2026-03-20',
                    'currency' => 'USD',
                ],
            ],
        ], $loggedMessages);
    }
}
