<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\Currency;
use Shopware\App\SDK\Context\SalesChannelContext\RoundingConfig;

#[CoversClass(Currency::class)]
class CurrencyTest extends TestCase
{
    public function testConstruct(): void
    {
        $rounding = [
            'decimals' => 2,
            'interval' => 0.01,
            'roundForNet' => true,
        ];

        $currency = new Currency([
            'id' => 'currency-id',
            'isoCode' => 'EUR',
            'factor' => 1.0,
            'symbol' => '€',
            'shortName' => 'Euro',
            'name' => 'Euro',
            'itemRounding' => $rounding,
            'totalRounding' => $rounding,
            'taxFreeFrom' => 100.0,
        ]);

        static::assertSame('currency-id', $currency->getId());
        static::assertSame('EUR', $currency->getIsoCode());
        static::assertSame(1.0, $currency->getFactor());
        static::assertSame('€', $currency->getSymbol());
        static::assertSame('Euro', $currency->getShortName());
        static::assertSame('Euro', $currency->getName());
        static::assertEquals(new RoundingConfig($rounding), $currency->getItemRounding());
        static::assertEquals(new RoundingConfig($rounding), $currency->getTotalRounding());
        static::assertSame(100.0, $currency->getTaxFreeFrom());
    }

    public function testGetTaxFreeFrom(): void
    {
        $currency = new Currency(['taxFreeFrom' => 100]);
        static::assertSame(100.0, $currency->getTaxFreeFrom());
        static::assertIsFloat($currency->getTaxFreeFrom());
    }
}
