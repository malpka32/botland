<?php

declare(strict_types=1);

namespace CurrencyRate\Application\History;

use CurrencyRate\Domain\Collection\NbpTableCollection;

interface RateHistoryWriterInterface
{
    public function replaceThirtyDays(NbpTableCollection $tableCollection, \DateTimeImmutable $cutoffDate): void;
}
