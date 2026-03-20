<?php

declare(strict_types=1);

namespace CurrencyRate\Application\ExchangeRate;

use CurrencyRate\Application\Log\DebugLoggerInterface;
use CurrencyRate\Application\Support\CurrencyIsoCode;
use CurrencyRate\Domain\Collection\NbpTableCollection;
use CurrencyRate\Domain\Dto\Api\NbpCurrencySeries;
use CurrencyRate\Domain\Dto\Api\NbpTable;
use CurrencyRate\Infrastructure\Endpoint\CurrencySeriesEndpoint;
use CurrencyRate\Infrastructure\Endpoint\LastTableEndpoint;
use CurrencyRate\Infrastructure\Endpoint\LatestCurrencyRateEndpoint;
use CurrencyRate\Infrastructure\Endpoint\NbpEndpointFactory;
use CurrencyRate\Infrastructure\Endpoint\TableRangeEndpoint;
use CurrencyRate\Infrastructure\Http\NbpHttpClient;
use CurrencyRate\Infrastructure\Mapper\Api\NbpCurrencySeriesMapper;
use CurrencyRate\Infrastructure\Mapper\Api\NbpTableMapper;

final class NbpApiGateway implements ExchangeRateGatewayInterface
{
    public function __construct(
        private NbpHttpClient $httpClient,
        private NbpEndpointFactory $endpointFactory,
        private NbpTableMapper $nbpTableMapper,
        private NbpCurrencySeriesMapper $currencySeriesMapper,
        private DebugLoggerInterface $logger
    ) {
    }

    public function fetchLatestTable(string $tableType): NbpTable
    {
        $endpoint = $this->resolveLastTableEndpoint($tableType);
        $path = $endpoint->buildPath();
        $this->logger->log('Fetching latest NBP table', ['table' => CurrencyIsoCode::normalize($tableType), 'path' => $path]);
        $json = $this->httpClient->get($path);
        $collection = $this->nbpTableMapper->mapCollection($json);
        $table = $collection->first();
        if ($table === null) {
            $this->logger->log('Latest NBP table is empty', ['table' => CurrencyIsoCode::normalize($tableType)]);
            throw new \RuntimeException(
                sprintf('NBP latest table %s payload is empty.', CurrencyIsoCode::normalize($tableType))
            );
        }

        $this->logger->log('Fetched latest NBP table', [
            'table' => CurrencyIsoCode::normalize($tableType),
            'effective_date' => $table->effectiveDate(),
            'rates_count' => $table->rates()->count(),
        ]);

        return $table;
    }

    public function fetchLatestCurrencyRate(string $tableType, string $isoCode): NbpCurrencySeries
    {
        $endpoint = $this->resolveLatestCurrencyRateEndpoint($tableType);
        $path = $endpoint->buildPath($isoCode);
        $this->logger->log('Fetching latest NBP currency rate', [
            'table' => CurrencyIsoCode::normalize($tableType),
            'iso_code' => CurrencyIsoCode::normalize($isoCode),
            'path' => $path,
        ]);
        $json = $this->httpClient->get($path);

        $series = $this->currencySeriesMapper->map($json);
        $this->logger->log('Fetched latest NBP currency rate', [
            'table' => CurrencyIsoCode::normalize($tableType),
            'iso_code' => CurrencyIsoCode::normalize($isoCode),
            'rates_count' => $series->rates()->count(),
        ]);

        return $series;
    }

    public function fetchTableRange(
        string $tableType,
        \DateTimeImmutable $startDate,
        \DateTimeImmutable $endDate
    ): NbpTableCollection {
        $endpoint = $this->resolveTableRangeEndpoint($tableType);
        $path = $endpoint->buildPath($startDate, $endDate);
        $this->logger->log('Fetching NBP table range', [
            'table' => CurrencyIsoCode::normalize($tableType),
            'start_date' => $startDate->format('Y-m-d'),
            'end_date' => $endDate->format('Y-m-d'),
            'path' => $path,
        ]);
        $json = $this->httpClient->get($path);

        $collection = $this->nbpTableMapper->mapCollection($json);
        $this->logger->log('Fetched NBP table range', [
            'table' => CurrencyIsoCode::normalize($tableType),
            'tables_count' => $collection->count(),
        ]);

        return $collection;
    }

    public function fetchCurrencySeries(string $tableType, string $isoCode, int $topCount = 1): NbpCurrencySeries
    {
        $endpoint = $this->resolveCurrencySeriesEndpoint($tableType);
        $path = $endpoint->buildPath($isoCode, $topCount);
        $this->logger->log('Fetching NBP currency series', [
            'table' => CurrencyIsoCode::normalize($tableType),
            'iso_code' => CurrencyIsoCode::normalize($isoCode),
            'top_count' => $topCount,
            'path' => $path,
        ]);
        $json = $this->httpClient->get($path);

        $series = $this->currencySeriesMapper->map($json);
        $this->logger->log('Fetched NBP currency series', [
            'table' => CurrencyIsoCode::normalize($tableType),
            'iso_code' => CurrencyIsoCode::normalize($isoCode),
            'rates_count' => $series->rates()->count(),
        ]);

        return $series;
    }

    private function resolveLastTableEndpoint(string $tableType): LastTableEndpoint
    {
        return $this->endpointFactory->createLastTableEndpoint($tableType);
    }

    private function resolveTableRangeEndpoint(string $tableType): TableRangeEndpoint
    {
        return $this->endpointFactory->createTableRangeEndpoint($tableType);
    }

    private function resolveCurrencySeriesEndpoint(string $tableType): CurrencySeriesEndpoint
    {
        return $this->endpointFactory->createCurrencySeriesEndpoint($tableType);
    }

    private function resolveLatestCurrencyRateEndpoint(string $tableType): LatestCurrencyRateEndpoint
    {
        return $this->endpointFactory->createLatestCurrencyRateEndpoint($tableType);
    }
}
