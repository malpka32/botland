<?php

declare(strict_types=1);

namespace CurrencyRate\Application;

use CurrencyRate\Application\ExchangeRate\CurrencyRateUpdater;
use CurrencyRate\Application\ExchangeRate\ExchangeRateGatewayInterface;
use CurrencyRate\Application\ExchangeRate\FallbackPlnRateLookupStrategy;
use CurrencyRate\Application\ExchangeRate\NbpApiGateway;
use CurrencyRate\Application\ExchangeRate\NbpExchangeRateCalculator;
use CurrencyRate\Application\ExchangeRate\NbpTablePlnRateLookupStrategy;
use CurrencyRate\Application\ExchangeRate\PlnRateLookupStrategyInterface;
use CurrencyRate\Application\History\CachedRateHistoryReader;
use CurrencyRate\Application\History\HistoryViewProvider;
use CurrencyRate\Application\History\RateHistoryReader;
use CurrencyRate\Application\History\RateHistoryReaderInterface;
use CurrencyRate\Application\History\RateHistorySynchronizer;
use CurrencyRate\Application\History\RateHistoryWriter;
use CurrencyRate\Application\History\RateHistoryWriterInterface;
use CurrencyRate\Infrastructure\Endpoint\CurrencySeriesEndpoint;
use CurrencyRate\Infrastructure\Endpoint\LastTableEndpoint;
use CurrencyRate\Infrastructure\Endpoint\TableRangeEndpoint;
use CurrencyRate\Infrastructure\Http\NbpHttpClient;
use CurrencyRate\Infrastructure\Mapper\NbpCurrencySeriesMapper;
use CurrencyRate\Infrastructure\Mapper\NbpTableMapper;

final class CurrencyRateServiceFactory
{
    private ?ClockInterface $clock = null;
    private ?CurrencyNameResolver $currencyNameResolver = null;
    private ?CurrencyProviderInterface $shopCurrencyProvider = null;
    private ?ExchangeRateGatewayInterface $nbpApiGateway = null;
    private ?NbpExchangeRateCalculator $nbpExchangeRateCalculator = null;
    private ?PlnRateLookupStrategyInterface $plnRateLookupStrategy = null;
    private ?CurrencyRateUpdater $currencyRateUpdater = null;
    private ?RateHistoryReaderInterface $rateHistoryReader = null;
    private ?RateHistoryWriterInterface $rateHistoryWriter = null;
    private ?RateHistorySynchronizer $rateHistorySynchronizer = null;
    private ?HistoryViewProvider $historyViewProvider = null;
    private ?ProductRateTableBuilder $productRateTableBuilder = null;

    public function clock(): ClockInterface
    {
        if ($this->clock === null) {
            $this->clock = new SystemClock();
        }

        return $this->clock;
    }

    public function currencyNameResolver(): CurrencyNameResolver
    {
        if ($this->currencyNameResolver === null) {
            $this->currencyNameResolver = new CurrencyNameResolver();
        }

        return $this->currencyNameResolver;
    }

    public function shopCurrencyProvider(): CurrencyProviderInterface
    {
        if ($this->shopCurrencyProvider === null) {
            $this->shopCurrencyProvider = new ShopCurrencyProvider();
        }

        return $this->shopCurrencyProvider;
    }

    public function nbpApiGateway(): ExchangeRateGatewayInterface
    {
        if ($this->nbpApiGateway === null) {
            $this->nbpApiGateway = new NbpApiGateway(
                new NbpHttpClient(),
                new LastTableEndpoint(),
                new TableRangeEndpoint(),
                new CurrencySeriesEndpoint(),
                new NbpTableMapper(),
                new NbpCurrencySeriesMapper()
            );
        }

        return $this->nbpApiGateway;
    }

    public function currencyRateUpdater(): CurrencyRateUpdater
    {
        if ($this->currencyRateUpdater === null) {
            $this->currencyRateUpdater = new CurrencyRateUpdater(
                $this->shopCurrencyProvider(),
                $this->nbpExchangeRateCalculator()
            );
        }

        return $this->currencyRateUpdater;
    }

    public function nbpExchangeRateCalculator(): NbpExchangeRateCalculator
    {
        if ($this->nbpExchangeRateCalculator === null) {
            $this->nbpExchangeRateCalculator = new NbpExchangeRateCalculator($this->plnRateLookupStrategy());
        }

        return $this->nbpExchangeRateCalculator;
    }

    public function plnRateLookupStrategy(): PlnRateLookupStrategyInterface
    {
        if ($this->plnRateLookupStrategy === null) {
            $primary = new NbpTablePlnRateLookupStrategy($this->nbpApiGateway(), 'A');
            $fallback = new NbpTablePlnRateLookupStrategy($this->nbpApiGateway(), 'B');
            $this->plnRateLookupStrategy = new FallbackPlnRateLookupStrategy($primary, $fallback);
        }

        return $this->plnRateLookupStrategy;
    }

    public function rateHistoryReader(): RateHistoryReaderInterface
    {
        if ($this->rateHistoryReader === null) {
            $reader = new RateHistoryReader(
                $this->shopCurrencyProvider(),
                $this->currencyNameResolver()
            );
            $this->rateHistoryReader = new CachedRateHistoryReader($reader);
        }

        return $this->rateHistoryReader;
    }

    public function rateHistoryWriter(): RateHistoryWriterInterface
    {
        if ($this->rateHistoryWriter === null) {
            $this->rateHistoryWriter = new RateHistoryWriter(
                $this->shopCurrencyProvider(),
                $this->clock()
            );
        }

        return $this->rateHistoryWriter;
    }

    public function rateHistorySynchronizer(): RateHistorySynchronizer
    {
        if ($this->rateHistorySynchronizer === null) {
            $this->rateHistorySynchronizer = new RateHistorySynchronizer(
                $this->nbpApiGateway(),
                $this->rateHistoryWriter(),
                $this->currencyRateUpdater(),
                $this->clock()
            );
        }

        return $this->rateHistorySynchronizer;
    }

    public function historyViewProvider(): HistoryViewProvider
    {
        if ($this->historyViewProvider === null) {
            $this->historyViewProvider = new HistoryViewProvider(
                $this->rateHistoryReader(),
                $this->shopCurrencyProvider(),
                $this->currencyNameResolver()
            );
        }

        return $this->historyViewProvider;
    }

    public function productRateTableBuilder(): ProductRateTableBuilder
    {
        if ($this->productRateTableBuilder === null) {
            $this->productRateTableBuilder = new ProductRateTableBuilder(
                $this->shopCurrencyProvider(),
                $this->currencyNameResolver()
            );
        }

        return $this->productRateTableBuilder;
    }
}
