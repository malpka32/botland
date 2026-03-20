<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Infrastructure\Endpoint;

use CurrencyRate\Infrastructure\Endpoint\CurrencySeriesEndpoint;
use CurrencyRate\Infrastructure\Endpoint\LastTableEndpoint;
use CurrencyRate\Infrastructure\Endpoint\LatestCurrencyRateEndpoint;
use CurrencyRate\Infrastructure\Endpoint\NbpEndpointFactory;
use CurrencyRate\Infrastructure\Endpoint\TableRangeEndpoint;
use PHPUnit\Framework\TestCase;

final class EndpointsTest extends TestCase
{
    public function testSimpleEndpointsBuildExpectedPaths(): void
    {
        self::assertSame(
            '/api/exchangerates/tables/A/last/1/?format=json',
            (new LastTableEndpoint('a'))->buildPath()
        );
        self::assertSame(
            '/api/exchangerates/rates/B/eur/?format=json',
            (new LatestCurrencyRateEndpoint('b'))->buildPath('EUR')
        );
        self::assertSame(
            '/api/exchangerates/tables/A/2026-03-01/2026-03-20/?format=json',
            (new TableRangeEndpoint('a'))->buildPath(new \DateTimeImmutable('2026-03-01'), new \DateTimeImmutable('2026-03-20'))
        );
        self::assertSame(
            '/api/exchangerates/rates/A/usd/last/5/?format=json',
            (new CurrencySeriesEndpoint('a'))->buildPath('USD', 5)
        );
    }

    public function testEndpointFactoryCreatesEndpointsAndValidatesTableType(): void
    {
        $factory = new NbpEndpointFactory();

        self::assertSame(
            '/api/exchangerates/tables/B/last/1/?format=json',
            $factory->createLastTableEndpoint(' b ')->buildPath()
        );
        self::assertSame(
            '/api/exchangerates/tables/A/2026-03-01/2026-03-20/?format=json',
            $factory->createTableRangeEndpoint('a')->buildPath(
                new \DateTimeImmutable('2026-03-01'),
                new \DateTimeImmutable('2026-03-20')
            )
        );
        self::assertSame(
            '/api/exchangerates/rates/B/chf/?format=json',
            $factory->createLatestCurrencyRateEndpoint('B')->buildPath('CHF')
        );

        $this->expectException(\InvalidArgumentException::class);
        $factory->createLastTableEndpoint('C');
    }
}
