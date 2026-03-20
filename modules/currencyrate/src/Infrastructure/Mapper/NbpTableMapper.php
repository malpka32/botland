<?php

declare(strict_types=1);

namespace CurrencyRate\Infrastructure\Mapper;

use CurrencyRate\Domain\Collection\NbpRateCollection;
use CurrencyRate\Domain\Collection\NbpTableCollection;
use CurrencyRate\Domain\Dto\NbpRate;
use CurrencyRate\Domain\Dto\NbpTable;

final class NbpTableMapper
{
    public function mapCollection(string $json): NbpTableCollection
    {
        $payload = json_decode($json, true);
        if (!is_array($payload)) {
            throw new \RuntimeException('Cannot decode NBP table payload.');
        }

        $tables = new NbpTableCollection();
        foreach ($payload as $tableRow) {
            if (!is_array($tableRow)) {
                continue;
            }

            $rates = new NbpRateCollection();
            $rateRows = $tableRow['rates'] ?? [];
            if (is_array($rateRows)) {
                foreach ($rateRows as $rateRow) {
                    if (!is_array($rateRow)) {
                        continue;
                    }

                    $code = strtoupper((string) ($rateRow['code'] ?? ''));
                    $currency = (string) ($rateRow['currency'] ?? '');
                    $mid = (float) ($rateRow['mid'] ?? 0);
                    if ($code === '' || $currency === '' || $mid <= 0) {
                        continue;
                    }

                    $rates->add(new NbpRate($currency, $code, $mid));
                }
            }

            $tables->add(
                new NbpTable(
                    (string) ($tableRow['table'] ?? 'A'),
                    (string) ($tableRow['no'] ?? ''),
                    (string) ($tableRow['effectiveDate'] ?? ''),
                    $rates
                )
            );
        }

        return $tables;
    }
}
