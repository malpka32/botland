<?php

declare(strict_types=1);

namespace CurrencyRate\Domain\Collection;

use CurrencyRate\Domain\Dto\Api\NbpTable;

/**
 * @extends AbstractTypedCollection<NbpTable>
 */
final class NbpTableCollection extends AbstractTypedCollection
{
    public function add(NbpTable $table): void
    {
        $this->addItem($table);
    }

    public function first(): ?NbpTable
    {
        return $this->items[0] ?? null;
    }
}
