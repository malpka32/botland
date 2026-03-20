<?php

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

use CurrencyRate\Application\CurrencyRateServiceFactory;
use CurrencyRate\Application\ProductRateTableBuilder;
use CurrencyRate\Application\History\HistoryViewProvider;

final class CurrencyRate extends Module
{
    private const DEBUG_LOG_ENABLED_KEY = 'CURRENCYRATE_DEBUG_LOG_ENABLED';
    private ?CurrencyRateServiceFactory $serviceFactory = null;

    public function __construct()
    {
        $this->name = 'currencyrate';
        $this->tab = 'pricing_promotion';
        $this->version = '1.0.0';
        $this->author = 'Recruitment Task';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => '9.9.999'];

        parent::__construct();

        $this->displayName = $this->trans('Currency Rate', [], 'Modules.Currencyrate.Admin');
        $this->description = $this->trans(
            'Shows 30-day currency history and product prices converted by current rates from NBP.',
            [],
            'Modules.Currencyrate.Admin'
        );
    }

    public function install(): bool
    {
        if (!parent::install()) {
            return false;
        }

        if (
            !$this->registerHook('displayHeader')
            || !$this->registerHook('displayProductAdditionalInfo')
            || !$this->installDatabase()
        ) {
            return false;
        }

        Configuration::updateValue(self::DEBUG_LOG_ENABLED_KEY, '0');

        return true;
    }

    public function uninstall(): bool
    {
        Configuration::deleteByName(self::DEBUG_LOG_ENABLED_KEY);

        return $this->uninstallDatabase()
            && parent::uninstall();
    }

    public function getContent(): string
    {
        $syncStatus = null;
        $settingsStatus = null;

        if (Tools::isSubmit('submitCurrencyRateSettings')) {
            $enabled = Tools::getValue('currencyrate_debug_log_enabled') === '1' ? '1' : '0';
            Configuration::updateValue(self::DEBUG_LOG_ENABLED_KEY, $enabled);
            $settingsStatus = 'saved';
        }

        if (Tools::isSubmit('submitCurrencyRateSync')) {
            try {
                $this->getServiceFactory()->rateHistorySynchronizer()->syncLastThirtyDays();
                $syncStatus = 'success';
            } catch (Throwable $exception) {
                PrestaShopLogger::addLog('[currencyrate] ' . $exception->getMessage(), 3);
                $syncStatus = 'error';
            }
        }

        $this->context->smarty->assign([
            'currencyrate_sync_status' => $syncStatus,
            'currencyrate_settings_status' => $settingsStatus,
            'currencyrate_debug_log_enabled' => (string) Configuration::get(self::DEBUG_LOG_ENABLED_KEY) === '1',
        ]);

        return $this->fetch('module:currencyrate/views/templates/admin/configure.tpl');
    }

    /**
     * @param array<string, mixed> $params
     */
    public function hookDisplayHeader(array $params): void
    {
        $controller = $this->context->controller;
        $phpSelf = isset($controller->php_self) ? (string) $controller->php_self : '';
        if ($phpSelf !== 'product') {
            return;
        }

        $this->context->controller->registerStylesheet(
            'module-currencyrate-product',
            'modules/' . $this->name . '/views/css/product.css',
            ['media' => 'all', 'priority' => 90]
        );
    }

    /**
     * @param array<string, mixed> $params
     */
    public function hookDisplayProductAdditionalInfo(array $params): string
    {
        $productId = 0;
        if (isset($params['product']['id_product'])) {
            $productId = (int) $params['product']['id_product'];
        } elseif (isset($params['product']->id_product)) {
            $productId = (int) $params['product']->id_product;
        } elseif (isset($params['product']['id'])) {
            $productId = (int) $params['product']['id'];
        }

        if ($productId <= 0) {
            return '';
        }

        $productAttributeId = isset($params['product']['id_product_attribute'])
            ? (int) $params['product']['id_product_attribute']
            : null;
        $rows = $this->buildProductRateTableBuilder()->buildForProduct($productId, $productAttributeId);
        if ($rows->count() === 0) {
            return '';
        }

        $this->context->smarty->assign([
            'currencyrate_rows' => $rows->toTemplateArray(),
        ]);

        return $this->fetch('module:currencyrate/views/templates/hook/product_rates.tpl');
    }

    public function getHistoryViewProvider(): HistoryViewProvider
    {
        return $this->getServiceFactory()->historyViewProvider();
    }

    private function installDatabase(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'currencyrate_history` (
            `id_currencyrate_history` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `iso_code` VARCHAR(3) NOT NULL,
            `effective_date` DATE NOT NULL,
            `mid` DECIMAL(20, 10) NOT NULL,
            `table_no` VARCHAR(24) NOT NULL,
            `table_type` VARCHAR(1) NOT NULL DEFAULT "A",
            `date_add` DATETIME NOT NULL,
            `date_upd` DATETIME NOT NULL,
            PRIMARY KEY (`id_currencyrate_history`),
            UNIQUE KEY `uniq_currency_day` (`iso_code`, `effective_date`),
            KEY `idx_effective_date` (`effective_date`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4;';

        return (bool) Db::getInstance()->execute($sql);
    }

    private function uninstallDatabase(): bool
    {
        $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . 'currencyrate_history`';

        return (bool) Db::getInstance()->execute($sql);
    }

    private function buildProductRateTableBuilder(): ProductRateTableBuilder
    {
        return $this->getServiceFactory()->productRateTableBuilder();
    }

    private function getServiceFactory(): CurrencyRateServiceFactory
    {
        if ($this->serviceFactory === null) {
            $this->serviceFactory = new CurrencyRateServiceFactory();
        }

        return $this->serviceFactory;
    }
}
