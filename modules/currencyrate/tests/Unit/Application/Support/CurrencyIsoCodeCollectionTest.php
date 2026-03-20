<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application\Support;

use CurrencyRate\Application\Support\CurrencyIsoCodeCollection;
use CurrencyRate\Domain\Collection\NbpRateCollection;
use CurrencyRate\Domain\Collection\NbpTableCollection;
use CurrencyRate\Domain\Dto\Api\NbpRate;
use CurrencyRate\Domain\Dto\Api\NbpTable;
use PHPUnit\Framework\TestCase;

final class CurrencyIsoCodeCollectionTest extends TestCase
{
    public function testConstructorNormalizesValuesAndSkipsEmptyAndPln(): void
    {
        $collection = new CurrencyIsoCodeCollection([' eur ', 'PLN', '', 'usd']);

        self::assertTrue($collection->contains('EUR'));
        self::assertTrue($collection->contains('usd'));
        self::assertFalse($collection->contains('PLN'));
        self::assertFalse($collection->contains(''));
    }

    public function testFromNbpTablesFiltersByTableType(): void
    {
        $tables = new NbpTableCollection();
        $tables->add($this->buildTable('A', ['EUR' => 4.2, 'USD' => 3.9]));
        $tables->add($this->buildTable('B', ['CHF' => 4.5]));

        $collection = CurrencyIsoCodeCollection::fromNbpTables($tables, 'A');

        self::assertTrue($collection->contains('EUR'));
        self::assertTrue($collection->contains('USD'));
        self::assertFalse($collection->contains('CHF'));
    }

    public function testMissingFromCollectionReturnsOnlyMissingIsoCodes(): void
    {
        $required = new CurrencyIsoCodeCollection(['EUR', 'USD', 'CHF']);
        $available = new CurrencyIsoCodeCollection(['EUR', 'CHF']);

        self::assertSame(['USD'], $required->missingFromCollection($available));
    }

    /**
     * @param array<string, float> $ratesByIso
     */
    private function buildTable(string $tableType, array $ratesByIso): NbpTable
    {
        $rates = new NbpRateCollection();
        foreach ($ratesByIso as $isoCode => $mid) {
            $rates->add(new NbpRate($isoCode, $isoCode, $mid));
        }

        return new NbpTable($tableType, '1/' . $tableType . '/NBP/2026', '2026-03-20', $rates);
    }
}
