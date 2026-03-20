<?php

declare(strict_types=1);

namespace CurrencyRate\Application\ExchangeRate;

use CurrencyRate\Domain\Collection\NbpTableCollection;
use CurrencyRate\Domain\Dto\NbpCurrencySeries;
use CurrencyRate\Domain\Dto\NbpTable;
use CurrencyRate\Infrastructure\Log\CurrencyRateDebugLogger;
use CurrencyRate\Infrastructure\Endpoint\CurrencySeriesEndpoint;
use CurrencyRate\Infrastructure\Endpoint\LastTableEndpoint;
use CurrencyRate\Infrastructure\Endpoint\TableRangeEndpoint;
use CurrencyRate\Infrastructure\Http\NbpHttpClient;
use CurrencyRate\Infrastructure\Mapper\NbpCurrencySeriesMapper;
use CurrencyRate\Infrastructure\Mapper\NbpTableMapper;

final class NbpApiGateway implements ExchangeRateGatewayInterface
{
    public function __construct(
        private NbpHttpClient $httpClient,
        private LastTableEndpoint $lastTableEndpoint,
        private TableRangeEndpoint $tableRangeEndpoint,
        private CurrencySeriesEndpoint $currencySeriesEndpoint,
        private NbpTableMapper $nbpTableMapper,
        private NbpCurrencySeriesMapper $currencySeriesMapper
    ) {
    }

    public function fetchLatestTableA(): NbpTable
    {
        return $this->fetchLatestTable('A');
    }

    public function fetchLatestTableB(): NbpTable
    {
        return $this->fetchLatestTable('B');
    }

    public function fetchLatestTable(string $tableType): NbpTable
    {
        $endpoint = $this->resolveLastTableEndpoint($tableType);
        $path = $endpoint->buildPath();
        CurrencyRateDebugLogger::log('Fetching latest NBP table', ['table' => strtoupper($tableType), 'path' => $path]);
        $json = $this->httpClient->get($path);
        $collection = $this->nbpTableMapper->mapCollection($json);
        $table = $collection->first();
        if ($table === null) {
            CurrencyRateDebugLogger::log('Latest NBP table is empty', ['table' => strtoupper($tableType)]);
            throw new \RuntimeException(sprintf('NBP latest table %s payload is empty.', strtoupper($tableType)));
        }

        CurrencyRateDebugLogger::log('Fetched latest NBP table', [
            'table' => strtoupper($tableType),
            'effective_date' => $table->effectiveDate(),
            'rates_count' => $table->rates()->count(),
        ]);

        return $table;
    }

    public function fetchTableARange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): NbpTableCollection
    {
        return $this->fetchTableRange('A', $startDate, $endDate);
    }

    public function fetchTableBRange(\DateTimeImmutable $startDate, \DateTimeImmutable $endDate): NbpTableCollection
    {
        return $this->fetchTableRange('B', $startDate, $endDate);
    }

    public function fetchTableRange(
        string $tableType,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate
    ): NbpTableCollection {
        $endpoint = $this->resolveTableRangeEndpoint($tableType);
        $path = $endpoint->buildPath($startDate, $endDate);
        CurrencyRateDebugLogger::log('Fetching NBP table range', [
            'table' => strtoupper($tableType),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'path' => $path,
        ]);
        $json = $this->httpClient->get($path);

        $collection = $this->nbpTableMapper->mapCollection($json);
        CurrencyRateDebugLogger::log('Fetched NBP table range', [
            'table' => strtoupper($tableType),
            'tables_count' => $collection->count(),
        ]);

        return $collection;
    }

    public function fetchCurrencySeriesA(string $isoCode, int $topCount = 1): NbpCurrencySeries
    {
        return $this->fetchCurrencySeries('A', $isoCode, $topCount);
    }

    public function fetchCurrencySeriesB(string $isoCode, int $topCount = 1): NbpCurrencySeries
    {
        return $this->fetchCurrencySeries('B', $isoCode, $topCount);
    }

    public function fetchCurrencySeries(string $tableType, string $isoCode, int $topCount = 1): NbpCurrencySeries
    {
        $endpoint = $this->resolveCurrencySeriesEndpoint($tableType);
        $path = $endpoint->buildPath($isoCode, $topCount);
        CurrencyRateDebugLogger::log('Fetching NBP currency series', [
            'table' => strtoupper($tableType),
            'iso_code' => strtoupper($isoCode),
            'top_count' => $topCount,
            'path' => $path,
        ]);
        $json = $this->httpClient->get($path);

        $series = $this->currencySeriesMapper->map($json);
        CurrencyRateDebugLogger::log('Fetched NBP currency series', [
            'table' => strtoupper($tableType),
            'iso_code' => strtoupper($isoCode),
            'rates_count' => $series->rates()->count(),
        ]);

        return $series;
    }

    private function resolveLastTableEndpoint(string $tableType): LastTableEndpoint
    {
        if (strtoupper($tableType) === 'A') {
            return $this->lastTableEndpoint;
        }

        return new LastTableEndpoint($tableType);
    }

    private function resolveTableRangeEndpoint(string $tableType): TableRangeEndpoint
    {
        if (strtoupper($tableType) === 'A') {
            return $this->tableRangeEndpoint;
        }

        return new TableRangeEndpoint($tableType);
    }

    private function resolveCurrencySeriesEndpoint(string $tableType): CurrencySeriesEndpoint
    {
        if (strtoupper($tableType) === 'A') {
            return $this->currencySeriesEndpoint;
        }

        return new CurrencySeriesEndpoint($tableType);
    }
}
