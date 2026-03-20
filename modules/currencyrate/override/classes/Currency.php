<?php
/**
 * Override legacy Currency refresh for built-in PrestaShop cron script.
 */
class Currency extends CurrencyCore
{
    /**
     * Refresh Currencies.
     *
     * @return string Error message
     */
    public static function refreshCurrencies()
    {
        $module = Module::getInstanceByName('currencyrate');
        if (!$module instanceof CurrencyRate || !$module->active) {
            return parent::refreshCurrencies();
        }

        try {
            $serviceId = CurrencyRate\Application\ExchangeRate\CurrencyRateUpdater::class;
            if (!$module->has($serviceId)) {
                return parent::refreshCurrencies();
            }

            $updater = $module->get($serviceId);
            if (!$updater instanceof CurrencyRate\Application\ExchangeRate\CurrencyRateUpdater) {
                return parent::refreshCurrencies();
            }

            $updater->refreshCurrentRates();

            return '';
        } catch (Throwable $exception) {
            PrestaShopLogger::addLog(
                sprintf('[currencyrate] Currency cron refresh failed: %s', $exception->getMessage()),
                3
            );

            return Context::getContext()->getTranslator()->trans(
                'Cannot refresh currencies.',
                [],
                'Admin.Notifications.Error'
            );
        }
    }
}
