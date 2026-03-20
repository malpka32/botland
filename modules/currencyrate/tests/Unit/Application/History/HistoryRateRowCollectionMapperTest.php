<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application\History;

use CurrencyRate\Application\CurrencyDisplayNameByIsoResolver;
use CurrencyRate\Application\CurrencyNameResolver;
use CurrencyRate\Application\CurrencyProviderInterface;
use CurrencyRate\Application\History\HistoryRateRowCollectionMapper;
use CurrencyRate\Domain\Collection\HistoryRateRecordCollection;
use CurrencyRate\Domain\Dto\Persistence\HistoryRateRecord;
use PHPUnit\Framework\TestCase;

final class HistoryRateRowCollectionMapperTest extends TestCase
{
    public function testMapBuildsViewRowsFromHistoryRecords(): void
    {
        $resolver = new CurrencyDisplayNameByIsoResolver(
            new class implements CurrencyProviderInterface {
                public function getActiveCurrenciesIndexedByIsoCode(): array
                {
                    return [];
                }
            },
            new CurrencyNameResolver()
        );

        $records = new HistoryRateRecordCollection();
        $records->add(new HistoryRateRecord('2026-03-20', 'EUR', 4.2));

        $mapped = (new HistoryRateRowCollectionMapper($resolver))->map($records);

        self::assertSame(
            [['effective_date' => '2026-03-20', 'iso_code' => 'EUR', 'currency_name' => 'EUR', 'mid' => 4.2]],
            $mapped->toTemplateArray()
        );
    }
}
