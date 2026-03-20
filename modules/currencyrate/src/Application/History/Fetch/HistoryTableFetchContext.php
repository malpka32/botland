<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History\Fetch;

use CurrencyRate\Application\Support\CurrencyIsoCodeCollection;
use CurrencyRate\Domain\Collection\NbpTableCollection;

final class HistoryTableFetchContext
{
    private NbpTableCollection $tables;

    public function __construct(
        private \DateTimeImmutable $startDate,
        private \DateTimeImmutable $endDate,
        private CurrencyIsoCodeCollection $activeIsoCodes
    ) {
        $this->tables = new NbpTableCollection();
    }

    public function startDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function endDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function activeIsoCodes(): CurrencyIsoCodeCollection
    {
        return $this->activeIsoCodes;
    }

    public function tables(): NbpTableCollection
    {
        return $this->tables;
    }

    public function addTables(NbpTableCollection $collection): void
    {
        foreach ($collection as $table) {
            $this->tables->add($table);
        }
    }
}
