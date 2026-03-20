<?php

declare(strict_types=1);

namespace CurrencyRate\Infrastructure\Mapper\Api;

use CurrencyRate\Application\Support\CurrencyIsoCode;
use CurrencyRate\Domain\Collection\NbpSeriesRateCollection;
use CurrencyRate\Domain\Dto\Api\NbpCurrencySeries;
use CurrencyRate\Domain\Dto\Api\NbpSeriesRate;

final class NbpCurrencySeriesMapper
{
    public function map(string $json): NbpCurrencySeries
    {
        $payload = json_decode($json, true);
        if (!is_array($payload)) {
            throw new \RuntimeException('Cannot decode NBP currency series payload.');
        }

        $rates = new NbpSeriesRateCollection();
        $rateRows = $payload['rates'] ?? [];
        if (is_array($rateRows)) {
            foreach ($rateRows as $rateRow) {
                if (!is_array($rateRow)) {
                    continue;
                }

                $no = (string) ($rateRow['no'] ?? '');
                $effectiveDate = (string) ($rateRow['effectiveDate'] ?? '');
                $mid = (float) ($rateRow['mid'] ?? 0);
                if ($no === '' || $effectiveDate === '' || $mid <= 0) {
                    continue;
                }

                $rates->add(new NbpSeriesRate($no, $effectiveDate, $mid));
            }
        }

        return new NbpCurrencySeries(
            (string) ($payload['table'] ?? 'A'),
            (string) ($payload['currency'] ?? ''),
            CurrencyIsoCode::normalize((string) ($payload['code'] ?? '')),
            $rates
        );
    }
}
