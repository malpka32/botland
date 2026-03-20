<?php

declare(strict_types=1);

if (!defined('_PS_VERSION_')) {
    exit;
}

$composerAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
}

use CurrencyRate\Application\Module\ModuleRuntime;
use CurrencyRate\Infrastructure\Database\PrestaShopDatabaseAdapter;
use CurrencyRate\Infrastructure\Install\ModuleDatabaseInstaller;

final class CurrencyRate extends Module
{
    private const DEBUG_LOG_ENABLED_KEY = 'CURRENCYRATE_DEBUG_LOG_ENABLED';

    public function __construct()
    {
        $this->name = 'currencyrate';
        $this->tab = 'pricing_promotion';
        $this->version = '1.0.0';
        $this->author = 'PD Mentis Dawid Ples';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = ['min' => '8.0.0', 'max' => '9.0.3'];

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
            || !$this->createModuleDatabaseInstaller()->install()
        ) {
            return false;
        }

        Configuration::updateValue(self::DEBUG_LOG_ENABLED_KEY, '0');

        return true;
    }

    public function uninstall(): bool
    {
        Configuration::deleteByName(self::DEBUG_LOG_ENABLED_KEY);

        return $this->createModuleDatabaseInstaller()->uninstall()
            && parent::uninstall();
    }

    public function getContent(): string
    {
        $route = $this->get('router')->generate('currencyrate_admin_config');
        Tools::redirectAdmin($route);

        return '';
    }

    public function __call(string $name, array $arguments)
    {
        return $this->getModuleRuntime()->dispatchHook($name, $arguments);
    }

    private function getModuleRuntime(): ModuleRuntime
    {
        $runtime = $this->get(ModuleRuntime::class);
        if (!$runtime instanceof ModuleRuntime) {
            throw new RuntimeException(sprintf('Service "%s" has invalid type.', ModuleRuntime::class));
        }

        return $runtime;
    }

    private function createModuleDatabaseInstaller(): ModuleDatabaseInstaller
    {
        return new ModuleDatabaseInstaller(new PrestaShopDatabaseAdapter());
    }
}
