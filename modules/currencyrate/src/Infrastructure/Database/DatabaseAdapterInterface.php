<?php

declare(strict_types=1);

namespace CurrencyRate\Infrastructure\Database;

interface DatabaseAdapterInterface
{
    public function execute(string $sql): bool;

    /**
     * @return list<array<string, mixed>>
     */
    public function executeQuery(\DbQuery $query): array;

    /**
     * @param array<string, mixed> $data
     */
    public function insertOnDuplicateKey(string $table, array $data): bool;
}
