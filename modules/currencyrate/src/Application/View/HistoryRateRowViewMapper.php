<?php

declare(strict_types=1);

namespace CurrencyRate\Application\View;

use CurrencyRate\Domain\Collection\HistoryRateRowCollection;
use CurrencyRate\Domain\Dto\Shop\HistoryRateRow;

final class HistoryRateRowViewMapper
{
    /**
     * @return list<array{effective_date: string, iso_code: string, currency_name: string, mid: float}>
     */
    public function mapCollection(HistoryRateRowCollection $collection): array
    {
        $rows = [];
        foreach ($collection as $item) {
            if (!$item instanceof HistoryRateRow) {
                continue;
            }

            $rows[] = [
                'effective_date' => $item->effectiveDate(),
                'iso_code' => $item->isoCode(),
                'currency_name' => $item->currencyName(),
                'mid' => $item->mid(),
            ];
        }

        return $rows;
    }
}
