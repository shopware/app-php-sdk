<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\CalculatedPrice;
use Shopware\App\SDK\Framework\Collection;

#[CoversClass(CalculatedPrice::class)]
class CalculatedPriceTest extends TestCase
{
    public function testSum(): void
    {
        $calculatedPrice1 = new CalculatedPrice([
            'unitPrice' => 10.0,
            'quantity' => 2,
            'totalPrice' => 20.0,
            'calculatedTaxes' => [
                [
                    'taxRate' => 19.0,
                    'price' => 10.0,
                    'tax' => 1.9,
                ],
            ],
            'taxRules' => [
                [
                    'taxRate' => 19.0,
                    'percentage' => 100.0,
                ],
            ]
        ]);


        $calculatedPrice2 = new CalculatedPrice([
            'unitPrice' => 15.0,
            'quantity' => 3,
            'totalPrice' => 45.0,
            'calculatedTaxes' => [
                [
                    'taxRate' => 19.0,
                    'price' => 5.0,
                    'tax' => 0.85,
                ],
                [
                    'taxRate' => 7.0,
                    'price' => 10.0,
                    'tax' => 0.7,
                ],
            ],
            'taxRules' => [
                [
                    'taxRate' => 19.0,
                    'percentage' => 33.3,
                ],
                [
                    'taxRate' => 7.0,
                    'percentage' => 66.7,
                ],
            ]
        ]);

        $sum = CalculatedPrice::sum(new Collection([
            $calculatedPrice1,
            $calculatedPrice2,
        ]));

        static::assertSame(25.0, $sum->getUnitPrice());
        static::assertSame(65.0, $sum->getTotalPrice());
        static::assertSame(1, $sum->getQuantity());
        static::assertCount(2, $sum->getCalculatedTaxes());
        static::assertSame(19.0, $sum->getCalculatedTaxes()->get('19')?->getTaxRate());
        static::assertSame(2.75, $sum->getCalculatedTaxes()->get('19')?->getTax());
        static::assertSame(15.0, $sum->getCalculatedTaxes()->get('19')?->getPrice());
        static::assertSame(7.0, $sum->getCalculatedTaxes()->get('7')?->getTaxRate());
        static::assertSame(0.7, $sum->getCalculatedTaxes()->get('7')?->getTax());
        static::assertSame(10.0, $sum->getCalculatedTaxes()->get('7')?->getPrice());

        static::assertCount(3, $sum->getTaxRules());
        static::assertSame(19.0, $sum->getTaxRules()->get(0)?->getTaxRate());
        static::assertSame(100.0, $sum->getTaxRules()->get(0)?->getPercentage());
        static::assertSame(19.0, $sum->getTaxRules()->get(1)?->getTaxRate());
        static::assertSame(33.3, $sum->getTaxRules()->get(1)?->getPercentage());
        static::assertSame(7.0, $sum->getTaxRules()->get(2)?->getTaxRate());
        static::assertSame(66.7, $sum->getTaxRules()->get(2)?->getPercentage());
    }
}
