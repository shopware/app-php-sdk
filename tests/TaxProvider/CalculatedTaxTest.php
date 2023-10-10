<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\TaxProvider;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\TaxProvider\CalculatedTax;

#[CoversClass(CalculatedTax::class)]
class CalculatedTaxTest extends TestCase
{
    public function testConstruct(): void
    {
        $tax = new CalculatedTax(0.19, 1.0, 0.19);

        static::assertSame(0.19, $tax->tax);
        static::assertSame(1.0, $tax->taxRate);
        static::assertSame(0.19, $tax->price);

        static::assertSame(['tax' => 0.19, 'taxRate' => 1.0, 'price' => 0.19], $tax->jsonSerialize());
    }
}
