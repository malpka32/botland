<?php

declare(strict_types=1);

namespace CurrencyRate\Infrastructure\Install;

use CurrencyRate\Infrastructure\Database\DatabaseAdapterInterface;

final class ModuleDatabaseInstaller
{
    private const INSTALL_SQL_PATH = 'sql/install.sql';
    private const UNINSTALL_SQL_PATH = 'sql/uninstall.sql';

    public function __construct(private DatabaseAdapterInterface $database)
    {
    }

    public function install(): bool
    {
        return $this->executeSqlFile(self::INSTALL_SQL_PATH);
    }

    public function uninstall(): bool
    {
        return $this->executeSqlFile(self::UNINSTALL_SQL_PATH);
    }

    private function executeSqlFile(string $relativePath): bool
    {
        $fullPath = $this->getModuleRootPath() . '/' . ltrim($relativePath, '/');
        if (!is_file($fullPath)) {
            $this->logError(sprintf('SQL file not found: %s', $fullPath));

            return false;
        }

        $content = file_get_contents($fullPath);
        if ($content === false) {
            $this->logError(sprintf('Cannot read SQL file: %s', $fullPath));

            return false;
        }

        $sql = strtr($content, [
            'PREFIX_' => _DB_PREFIX_,
            'ENGINE_' => _MYSQL_ENGINE_,
        ]);

        foreach ($this->splitStatements($sql) as $statement) {
            if (!$this->database->execute($statement)) {
                $this->logError(sprintf('SQL execution failed for file: %s', $fullPath));

                return false;
            }
        }

        return true;
    }

    /**
     * @return list<string>
     */
    private function splitStatements(string $sql): array
    {
        $statements = preg_split('/;\s*(?:\r?\n|$)/', $sql) ?: [];

        return array_values(array_filter(array_map('trim', $statements), static fn (string $statement): bool => $statement !== ''));
    }

    private function logError(string $message): void
    {
        \PrestaShopLogger::addLog('[currencyrate] ' . $message, 3);
    }

    private function getModuleRootPath(): string
    {
        return dirname(__DIR__, 3);
    }
}
