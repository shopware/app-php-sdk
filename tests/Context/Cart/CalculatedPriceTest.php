<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\Cart\CalculatedPrice;
use Shopware\App\SDK\Context\Cart\CalculatedTax;

#[CoversClass(CalculatedPrice::class)]
#[CoversClass(CalculatedTax::class)]
#[UsesClass(ArrayStruct::class)]
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


        $sum = CalculatedPrice::sum([
            $calculatedPrice1,
            $calculatedPrice2,
        ]);

        static::assertSame(25.0, $sum->getUnitPrice());
        static::assertSame(65.0, $sum->getTotalPrice());
        static::assertSame(1, $sum->getQuantity());
        static::assertCount(2, $sum->getCalculatedTaxes());
        static::assertSame(19.0, $sum->getCalculatedTaxes()[19.0]->getTaxRate());
        static::assertSame(2.75, $sum->getCalculatedTaxes()[19.0]->getTax());
        static::assertSame(15.0, $sum->getCalculatedTaxes()[19.0]->getPrice());
        static::assertSame(7.0, $sum->getCalculatedTaxes()[7.0]->getTaxRate());
        static::assertSame(0.7, $sum->getCalculatedTaxes()[7.0]->getTax());
        static::assertSame(10.0, $sum->getCalculatedTaxes()[7.0]->getPrice());
        static::assertCount(3, $sum->getTaxRules());
    }
}
