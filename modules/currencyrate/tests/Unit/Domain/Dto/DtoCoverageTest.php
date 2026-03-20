<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Domain\Dto;

use CurrencyRate\Domain\Collection\NbpRateCollection;
use CurrencyRate\Domain\Collection\NbpSeriesRateCollection;
use CurrencyRate\Domain\Dto\HistoryRateRow;
use CurrencyRate\Domain\Dto\Api\NbpCurrencySeries;
use CurrencyRate\Domain\Dto\Api\NbpRate;
use CurrencyRate\Domain\Dto\Api\NbpSeriesRate;
use CurrencyRate\Domain\Dto\Api\NbpTable;
use CurrencyRate\Domain\Dto\Persistence\HistoryRateRecord;
use CurrencyRate\Domain\Dto\ProductConvertedPrice;
use CurrencyRate\Domain\Dto\Shop\HistoryRateRow as ShopHistoryRateRow;
use CurrencyRate\Domain\Dto\Shop\ProductConvertedPrice as ShopProductConvertedPrice;
use PHPUnit\Framework\TestCase;

final class DtoCoverageTest extends TestCase
{
    public function testDomainNbpRateGetters(): void
    {
        $dto = new NbpRate('Euro', 'EUR', 4.12);

        self::assertSame('Euro', $dto->currency());
        self::assertSame('EUR', $dto->code());
        self::assertSame(4.12, $dto->mid());
    }

    public function testDomainNbpSeriesRateGetters(): void
    {
        $dto = new NbpSeriesRate('12/A/NBP/2026', '2026-03-20', 4.44);

        self::assertSame('12/A/NBP/2026', $dto->no());
        self::assertSame('2026-03-20', $dto->effectiveDate());
        self::assertSame(4.44, $dto->mid());
    }

    public function testDomainNbpTableAndSeriesGetters(): void
    {
        $rates = new NbpRateCollection();
        $rates->add(new NbpRate('Euro', 'EUR', 4.12));
        $table = new NbpTable('A', '12/A/NBP/2026', '2026-03-20', $rates);
        self::assertSame('A', $table->table());
        self::assertSame('12/A/NBP/2026', $table->number());
        self::assertSame('2026-03-20', $table->effectiveDate());
        self::assertSame($rates, $table->rates());

        $seriesRates = new NbpSeriesRateCollection();
        $seriesRates->add(new NbpSeriesRate('12/A/NBP/2026', '2026-03-20', 4.12));
        $series = new NbpCurrencySeries('A', 'Euro', 'EUR', $seriesRates);
        self::assertSame('A', $series->table());
        self::assertSame('Euro', $series->currency());
        self::assertSame('EUR', $series->code());
        self::assertSame($seriesRates, $series->rates());
    }

    public function testViewDtosAndPersistenceDto(): void
    {
        $historyRow = new HistoryRateRow('2026-03-20', 'EUR', 'Euro', 4.2);
        self::assertSame(
            [
                'effective_date' => '2026-03-20',
                'iso_code' => 'EUR',
                'currency_name' => 'Euro',
                'mid' => 4.2,
            ],
            $historyRow->toTemplateArray()
        );

        $convertedPrice = new ProductConvertedPrice('USD', 'US Dollar', '$', '12.34 $');
        self::assertSame(
            [
                'iso_code' => 'USD',
                'name' => 'US Dollar',
                'symbol' => '$',
                'formatted_price' => '12.34 $',
            ],
            $convertedPrice->toTemplateArray()
        );

        $record = new HistoryRateRecord('2026-03-20', 'EUR', 4.2);
        self::assertSame('2026-03-20', $record->effectiveDate());
        self::assertSame('EUR', $record->isoCode());
        self::assertSame(4.2, $record->mid());

        $shopRow = new ShopHistoryRateRow('2026-03-20', 'EUR', 'Euro', 4.2);
        self::assertSame('2026-03-20', $shopRow->effectiveDate());
        self::assertSame('EUR', $shopRow->isoCode());
        self::assertSame('Euro', $shopRow->currencyName());
        self::assertSame(4.2, $shopRow->mid());

        $shopPrice = new ShopProductConvertedPrice('USD', 'US Dollar', '$', '12.34 $');
        self::assertSame('USD', $shopPrice->isoCode());
        self::assertSame('US Dollar', $shopPrice->name());
        self::assertSame('$', $shopPrice->symbol());
        self::assertSame('12.34 $', $shopPrice->formattedPrice());
    }
}
