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
        $tax = new CalculatedTax(19.0, 100.0, 19.0);

        static::assertEquals(19.0, $tax->tax);
        static::assertEquals(100.0, $tax->taxRate);
        static::assertEquals(19.0, $tax->price);
    }

    public function testAdd(): void
    {
        $tax1 = new CalculatedTax(19.0, 100.0, 19.0);
        $tax2 = new CalculatedTax(19.0, 100.0, 19.0);

        $tax = $tax1->add($tax2);

        static::assertEquals(38.0, $tax->tax);
        static::assertEquals(100.0, $tax->taxRate);
        static::assertEquals(38.0, $tax->price);
    }

    public function testJsonSerialize(): void
    {
        $tax = new CalculatedTax(19.0, 100.0, 19.0);

        static::assertEquals(['tax' => 19.0, 'taxRate' => 100.0, 'price' => 19.0], $tax->jsonSerialize());
    }
}
