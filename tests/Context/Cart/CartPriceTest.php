<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\CalculatedTax;
use Shopware\App\SDK\Context\Cart\CartPrice;
use Shopware\App\SDK\Context\Cart\TaxRule;

#[CoversClass(CartPrice::class)]
class CartPriceTest extends TestCase
{
    public function testConstruct(): void
    {
        $tax = [
            'taxRate' => 19.0,
            'tax' => 0.19,
            'price' => 1.0,
        ];

        $taxRule = [
            'taxRate' => 1.0,
            'percentage' => 1.0,
        ];

        $cartPrice = new CartPrice([
            'netPrice' => 1.0,
            'totalPrice' => 1.19,
            'calculatedTaxes' => [$tax],
            'taxStatus' => 'gross',
            'taxRules' => [$taxRule],
            'positionPrice' => 1.0,
            'rawTotal' => 1.0,
        ]);

        static::assertSame(1.0, $cartPrice->getNetPrice());
        static::assertSame(1.19, $cartPrice->getTotalPrice());
        static::assertEquals(new CalculatedTax($tax), $cartPrice->getCalculatedTaxes()[0]);
        static::assertSame('gross', $cartPrice->getTaxStatus());
        static::assertEquals(new TaxRule($taxRule), $cartPrice->getTaxRules()[0]);
        static::assertSame(1.0, $cartPrice->getPositionPrice());
        static::assertSame(1.0, $cartPrice->getRawTotal());
    }
}
