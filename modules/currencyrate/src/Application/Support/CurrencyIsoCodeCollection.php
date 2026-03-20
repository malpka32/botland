<?php

declare(strict_types=1);

namespace CurrencyRate\Application\Support;

use CurrencyRate\Domain\Dto\Api\NbpRate;
use CurrencyRate\Domain\Dto\Api\NbpTable;

final class CurrencyIsoCodeCollection
{
    /** @var array<string, true> */
    private array $items = [];

    /**
     * @param iterable<string> $isoCodes
     */
    public function __construct(iterable $isoCodes = [])
    {
        foreach ($isoCodes as $isoCode) {
            $normalizedIsoCode = CurrencyIsoCode::normalize($isoCode);
            if ($normalizedIsoCode === '' || $normalizedIsoCode === 'PLN') {
                continue;
            }

            $this->items[$normalizedIsoCode] = true;
        }
    }

    public function contains(string $isoCode): bool
    {
        return isset($this->items[CurrencyIsoCode::normalize($isoCode)]);
    }

    /**
     * @param iterable<NbpTable> $tables
     */
    public static function fromNbpTables(iterable $tables, ?string $tableType = null): self
    {
        $isoCodes = [];
        $normalizedTableType = $tableType !== null ? CurrencyIsoCode::normalize($tableType) : null;
        foreach ($tables as $table) {
            if (!$table instanceof NbpTable) {
                continue;
            }

            if ($normalizedTableType !== null && CurrencyIsoCode::normalize($table->table()) !== $normalizedTableType) {
                continue;
            }

            foreach ($table->rates() as $rate) {
                if (!$rate instanceof NbpRate) {
                    continue;
                }

                $isoCodes[] = $rate->code();
            }
        }

        return new self($isoCodes);
    }

    /**
     * @return list<string>
     */
    public function missingFromCollection(self $availableIsoCodes): array
    {
        $missing = [];
        foreach ($this->isoCodes() as $isoCode) {
            if (!$availableIsoCodes->contains($isoCode)) {
                $missing[] = $isoCode;
            }
        }

        return $missing;
    }

    /**
     * @return list<string>
     */
    private function isoCodes(): array
    {
        return array_keys($this->items);
    }
}
