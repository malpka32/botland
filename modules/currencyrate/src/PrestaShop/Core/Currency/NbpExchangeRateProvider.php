<?php

declare(strict_types=1);

namespace CurrencyRate\PrestaShop\Core\Currency;

use CurrencyRate\Application\Support\CurrencyIsoCode;
use CurrencyRate\Application\ExchangeRate\NbpExchangeRateCalculator;
use PrestaShop\CircuitBreaker\Contract\CircuitBreakerInterface;
use PrestaShop\Decimal\DecimalNumber;
use PrestaShop\PrestaShop\Core\Currency\Exception\CurrencyFeedException;
use PrestaShop\PrestaShop\Core\Currency\ExchangeRateProvider;
use Symfony\Component\Cache\Adapter\AdapterInterface as CacheInterface;

final class NbpExchangeRateProvider extends ExchangeRateProvider
{
    private const DEFAULT_SHOP_CURRENCY_ISO = 'PLN';

    public function __construct(
        string $currencyFeedUrl,
        string $defaultCurrencyIsoCode,
        CircuitBreakerInterface $remoteServiceProvider = new PassThroughCircuitBreaker(),
        CacheInterface $cache = new ArrayAdapter(),
        private NbpExchangeRateCalculator $nbpExchangeRateCalculator,
        private string $shopDefaultCurrencyIsoCode = ''
    ) {
        $this->shopDefaultCurrencyIsoCode = CurrencyIsoCode::normalize($defaultCurrencyIsoCode);
        parent::__construct(
            $currencyFeedUrl,
            $defaultCurrencyIsoCode,
            $remoteServiceProvider,
            $cache
        );
    }

    /**
     * @param string $currencyIsoCode
     *
     * @throws CurrencyFeedException
     */
    public function getExchangeRate($currencyIsoCode)
    {
        if (!$this->isDefaultCurrencyPln()) {
            return parent::getExchangeRate($currencyIsoCode);
        }

        $targetIsoCode = CurrencyIsoCode::normalize((string) $currencyIsoCode);
        if ($targetIsoCode === self::DEFAULT_SHOP_CURRENCY_ISO) {
            return new DecimalNumber('1.0');
        }

        try {
            $exchangeRate = $this->nbpExchangeRateCalculator->calculatePrestaConversionRate(
                $targetIsoCode,
                self::DEFAULT_SHOP_CURRENCY_ISO,
                1.0
            );

            if ($exchangeRate === null || $exchangeRate <= 0.0) {
                throw new CurrencyFeedException(
                    sprintf('Cannot resolve NBP rate for target currency "%s".', $targetIsoCode)
                );
            }

            return new DecimalNumber(sprintf('%.10F', $exchangeRate));
        } catch (CurrencyFeedException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            throw new CurrencyFeedException($exception->getMessage(), 0, $exception);
        }
    }

    private function isDefaultCurrencyPln(): bool
    {
        return $this->shopDefaultCurrencyIsoCode === self::DEFAULT_SHOP_CURRENCY_ISO;
    }
}
