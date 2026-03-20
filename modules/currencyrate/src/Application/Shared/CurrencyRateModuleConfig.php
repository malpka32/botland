<?php

declare(strict_types=1);

namespace CurrencyRate\Application\Shared;

final class CurrencyRateModuleConfig
{
    public const HISTORY_WINDOW_DAYS = 30;
    public const CACHE_TTL_SECONDS = 300;
    public const PRICE_PRECISION = 6;
    public const RATE_UPDATE_EPSILON = 0.000001;
}
