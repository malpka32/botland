<?php

declare(strict_types=1);

namespace CurrencyRate\Application\Hook;

final class HookRegistry
{
    private const MODULE_HOOK_METHOD_PREFIX = 'hook';

    /** @var array<string, HookHandlerInterface> */
    private array $handlersByName = [];

    /**
     * @param iterable<HookHandlerInterface> $handlers
     */
    public function __construct(iterable $handlers)
    {
        foreach ($handlers as $handler) {
            $hookName = $handler->hookName();
            if (isset($this->handlersByName[$hookName])) {
                throw new \RuntimeException(sprintf('Duplicate hook handler registered for "%s".', $hookName));
            }

            $this->handlersByName[$hookName] = $handler;
        }
    }

    /**
     * @return list<string>
     */
    public function getRegisteredHookNames(): array
    {
        return array_values(array_keys($this->handlersByName));
    }

    public function registerInModule(\Module $module): bool
    {
        foreach ($this->getRegisteredHookNames() as $hookName) {
            if (!$module->registerHook($hookName)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<string, mixed> $payload
     *
     * @return mixed
     */
    public function handle(string $hookName, array $payload = [])
    {
        if (!isset($this->handlersByName[$hookName])) {
            return null;
        }

        return $this->handlersByName[$hookName]->handle($payload);
    }

    /**
     * @param list<mixed> $arguments
     *
     * @return mixed
     */
    public function dispatchFromModuleMethod(string $methodName, array $arguments = [])
    {
        if (!str_starts_with($methodName, self::MODULE_HOOK_METHOD_PREFIX)) {
            throw new \BadMethodCallException(sprintf('Undefined method "%s".', $methodName));
        }

        $hookName = lcfirst(substr($methodName, strlen(self::MODULE_HOOK_METHOD_PREFIX)));
        $payload = $arguments[0] ?? [];
        if (!is_array($payload)) {
            $payload = [];
        }

        return $this->handle($hookName, $payload) ?? '';
    }
}
