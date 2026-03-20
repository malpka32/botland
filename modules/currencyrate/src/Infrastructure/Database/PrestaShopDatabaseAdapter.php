<?php

declare(strict_types=1);

namespace CurrencyRate\Infrastructure\Database;

final class PrestaShopDatabaseAdapter implements DatabaseAdapterInterface
{
    public function execute(string $sql): bool
    {
        return (bool) \Db::getInstance()->execute($sql);
    }

    public function executeQuery(\DbQuery $query): array
    {
        $rows = \Db::getInstance()->executeS((string) $query);

        return is_array($rows) ? $rows : [];
    }

    public function insertOnDuplicateKey(string $table, array $data): bool
    {
        return (bool) \Db::getInstance()->insert($table, $data, false, true, \Db::ON_DUPLICATE_KEY);
    }
}
