<?php

declare(strict_types=1);

namespace CurrencyRate\Application\Module;

use CurrencyRate\Application\Hook\HookRegistry;
use CurrencyRate\Infrastructure\Install\ModuleDatabaseInstaller;

final class ModuleRuntime
{
    public function __construct(
        private HookRegistry $hookRegistry,
        private ModuleDatabaseInstaller $moduleDatabaseInstaller
    ) {
    }

    public function install(\Module $module): bool
    {
        return $this->hookRegistry->registerInModule($module)
            && $this->moduleDatabaseInstaller->install();
    }

    public function uninstall(): bool
    {
        return $this->moduleDatabaseInstaller->uninstall();
    }

    /**
     * @param list<mixed> $arguments
     *
     * @return mixed
     */
    public function dispatchHook(string $methodName, array $arguments = [])
    {
        return $this->hookRegistry->dispatchFromModuleMethod($methodName, $arguments);
    }
}
