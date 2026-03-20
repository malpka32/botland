<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History;

use CurrencyRate\Application\Shared\CurrencyRateModuleConfig;

final class HistoryDateRange
{
    public static function today(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('today');
    }

    public static function defaultStartDateFrom(\DateTimeImmutable $endDate): \DateTimeImmutable
    {
        $startDate = $endDate->modify(sprintf('-%d days', CurrencyRateModuleConfig::HISTORY_WINDOW_DAYS));
        if ($startDate === false) {
            throw new \RuntimeException('Could not build start date.');
        }

        return $startDate;
    }
}
