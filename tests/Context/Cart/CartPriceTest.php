<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\CartPrice;

#[CoversClass(CartPrice::class)]
class CartPriceTest extends TestCase
{
    public function testConstruct(): void
    {
        $cartPrice = new CartPrice([
            'netPrice' => 100.0,
            'totalPrice' => 100.0,
            'calculatedTaxes' => [
                [
                    'taxRate' => 19.0,
                    'tax' => 19.0,
                    'price' => 100.0,
                ],
            ],
            'taxStatus' => 'gross',
            'taxRules' => [
                [
                    'taxRate' => 19.0,
                    'percentage' => 19.0,
                ],
            ],
            'positionPrice' => 100.0,
            'rawTotal' => 100.0,
        ]);

        static::assertSame(100.0, $cartPrice->getTotalPrice());
        static::assertSame(100.0, $cartPrice->getNetPrice());
        static::assertCount(1, $cartPrice->getCalculatedTaxes());
        static::assertSame(19.0, $cartPrice->getCalculatedTaxes()->first()?->getTaxRate());
        static::assertSame(19.0, $cartPrice->getCalculatedTaxes()->first()?->getTax());
        static::assertSame(100.0, $cartPrice->getCalculatedTaxes()->first()?->getPrice());
        static::assertSame('gross', $cartPrice->getTaxStatus());
        static::assertCount(1, $cartPrice->getTaxRules());
        static::assertSame(19.0, $cartPrice->getTaxRules()->first()?->getTaxRate());
        static::assertSame(19.0, $cartPrice->getTaxRules()->first()?->getPercentage());
        static::assertSame(100.0, $cartPrice->getPositionPrice());
        static::assertSame(100.0, $cartPrice->getRawTotal());
    }
}
