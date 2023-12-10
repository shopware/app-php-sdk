<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\CalculatedPrice;
use Shopware\App\SDK\Context\Cart\CalculatedTax;
use Shopware\App\SDK\Context\Cart\TaxRule;

#[CoversClass(CalculatedPrice::class)]
class CalculatedPriceTest extends TestCase
{
    public function testConstruct(): void
    {
        $price = new CalculatedPrice([
            'unitPrice' => 1.0,
            'totalPrice' => 2.0,
            'quantity' => 3,
            'calculatedTaxes' => [
                [
                    'taxRate' => 0.19,
                    'price' => 1.0,
                    'tax' => 0.19,
                ],
                [
                    'taxRate' => 0.07,
                    'price' => 1.0,
                    'tax' => 0.07,
                ],
            ],
            'taxRules' => [
                [
                    'taxRate' => 0.19,
                    'percentage' => 100.0,
                ],
                [
                    'taxRate' => 0.07,
                    'percentage' => 100.0,
                ],
            ],
        ]);

        $taxes = [
            new CalculatedTax([
                'taxRate' => 0.19,
                'price' => 1.0,
                'tax' => 0.19,
            ]),
            new CalculatedTax([
                'taxRate' => 0.07,
                'price' => 1.0,
                'tax' => 0.07,
            ]),
        ];

        $taxRules = [
            new TaxRule([
                'taxRate' => 0.19,
                'percentage' => 100.0,
            ]),
            new TaxRule([
                'taxRate' => 0.07,
                'percentage' => 100.0,
            ]),
        ];

        static::assertSame(1.0, $price->getUnitPrice());
        static::assertSame(2.0, $price->getTotalPrice());
        static::assertSame(3, $price->getQuantity());
        static::assertEquals($taxes, $price->getCalculatedTaxes());
        static::assertEquals($taxRules, $price->getTaxRules());
    }
}
