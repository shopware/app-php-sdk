<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\Currency;

#[CoversClass(Currency::class)]
class CurrencyTest extends TestCase
{
    public function testConstruct(): void
    {
        $currency = new Currency([
            'id' => 'test',
            'isoCode' => 'baz',
            'factor' => 1.0,
            'symbol' => 'bar',
            'shortName' => 'FOO',
            'name' => 'foofoo',
            'itemRounding' => ['decimals' => 2],
            'totalRounding' => ['decimals' => 2],
            'taxFreeFrom' => 100.0,
        ]);

        static::assertSame('test', $currency->getId());
        static::assertSame('baz', $currency->getIsoCode());
        static::assertSame(1.0, $currency->getFactor());
        static::assertSame('bar', $currency->getSymbol());
        static::assertSame('FOO', $currency->getShortName());
        static::assertSame('foofoo', $currency->getName());
        static::assertSame(2, $currency->getItemRounding()->getDecimals());
        static::assertSame(2, $currency->getTotalRounding()->getDecimals());
        static::assertSame(100.0, $currency->getTaxFreeFrom());
    }

    public function testGetTaxFreeFrom(): void
    {
        $currency = new Currency(['taxFreeFrom' => 100]);
        static::assertSame(100.0, $currency->getTaxFreeFrom());
        static::assertIsFloat($currency->getTaxFreeFrom());
    }
}
