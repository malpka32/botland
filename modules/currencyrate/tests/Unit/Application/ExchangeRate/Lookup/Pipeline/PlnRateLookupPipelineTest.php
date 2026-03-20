<?php

declare(strict_types=1);

namespace CurrencyRate\Tests\Unit\Application\ExchangeRate\Lookup\Pipeline;

use CurrencyRate\Application\ExchangeRate\Lookup\Pipeline\PlnRateLookupPipeline;
use CurrencyRate\Application\ExchangeRate\Lookup\Step\PlnRateLookupPipelineStepInterface;
use CurrencyRate\Application\Log\DebugLoggerInterface;
use PHPUnit\Framework\TestCase;

final class PlnRateLookupPipelineTest extends TestCase
{
    public function testResolveReturnsOneForPlnAndSkipsSteps(): void
    {
        $step = $this->createMock(PlnRateLookupPipelineStepInterface::class);
        $step->expects(self::never())->method('resolve');

        $pipeline = new PlnRateLookupPipeline([$step], $this->createLogger());

        self::assertSame(1.0, $pipeline->resolve(' pln '));
    }

    public function testResolveReturnsFirstPositiveRateFromSteps(): void
    {
        $firstStep = $this->createMock(PlnRateLookupPipelineStepInterface::class);
        $firstStep->expects(self::once())
            ->method('resolve')
            ->with('EUR')
            ->willReturn(null);

        $secondStep = $this->createMock(PlnRateLookupPipelineStepInterface::class);
        $secondStep->expects(self::once())
            ->method('resolve')
            ->with('EUR')
            ->willReturn(4.25);

        $thirdStep = $this->createMock(PlnRateLookupPipelineStepInterface::class);
        $thirdStep->expects(self::never())->method('resolve');

        $pipeline = new PlnRateLookupPipeline([$firstStep, $secondStep, $thirdStep], $this->createLogger());

        self::assertSame(4.25, $pipeline->resolve('eur'));
    }

    public function testResolveLogsAndReturnsNullWhenNoStepCanResolveRate(): void
    {
        $logger = $this->createMock(DebugLoggerInterface::class);
        $logger->expects(self::once())
            ->method('log')
            ->with(
                'PLN rate could not be resolved by pipeline',
                ['iso_code' => 'CHF']
            );

        $step = $this->createMock(PlnRateLookupPipelineStepInterface::class);
        $step->expects(self::once())->method('resolve')->with('CHF')->willReturn(0.0);

        $pipeline = new PlnRateLookupPipeline([$step], $logger);

        self::assertNull($pipeline->resolve('chf'));
    }

    private function createLogger(): DebugLoggerInterface
    {
        $logger = $this->createMock(DebugLoggerInterface::class);
        $logger->method('isEnabled')->willReturn(true);

        return $logger;
    }
}
