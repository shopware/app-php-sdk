<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\CalculatedTax;

#[CoversClass(CalculatedTax::class)]
class CalculatedTaxTest extends TestCase
{
    public function testConstruct(): void
    {
        $tax = new CalculatedTax([
            'taxRate' => 0.19,
            'price' => 1.0,
            'tax' => 0.19,
        ]);

        static::assertSame(0.19, $tax->getTaxRate());
        static::assertSame(1.0, $tax->getPrice());
        static::assertSame(0.19, $tax->getTax());
    }
}
