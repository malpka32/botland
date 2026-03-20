<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Domain\Collection;

use CurrencyRate\Domain\Collection\NbpRateCollection;
use CurrencyRate\Domain\Dto\NbpRate;
use PHPUnit\Framework\TestCase;

final class NbpRateCollectionTest extends TestCase
{
    public function testFindByCodeIsCaseInsensitive(): void
    {
        $collection = new NbpRateCollection();
        $collection->add(new NbpRate('euro', 'EUR', 4.2));
        $collection->add(new NbpRate('dolar amerykanski', 'USD', 3.8));

        $result = $collection->findByCode('usd');

        self::assertInstanceOf(NbpRate::class, $result);
        self::assertSame('USD', $result->code());
        self::assertSame(3.8, $result->mid());
    }

    public function testFindByCodeReturnsNullWhenMissing(): void
    {
        $collection = new NbpRateCollection();
        $collection->add(new NbpRate('euro', 'EUR', 4.2));

        self::assertNull($collection->findByCode('CHF'));
    }
}
