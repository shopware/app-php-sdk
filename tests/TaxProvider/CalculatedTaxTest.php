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
        $tax = new CalculatedTax(19.0, 100.0, 19.0, 'label');

        static::assertSame(19.0, $tax->tax);
        static::assertSame(100.0, $tax->taxRate);
        static::assertSame(19.0, $tax->price);
        static::assertSame('label', $tax->label);
    }

    public function testAdd(): void
    {
        $tax1 = new CalculatedTax(19.0, 100.0, 19.0, 'label 1');
        $tax2 = new CalculatedTax(19.0, 100.0, 19.0, 'label 2');

        $tax = $tax1->add($tax2);

        static::assertSame(38.0, $tax->tax);
        static::assertSame(100.0, $tax->taxRate);
        static::assertSame(38.0, $tax->price);
        static::assertSame('label 1 + label 2', $tax->label);
    }

    public function testAddWithoutSomeLabels(): void
    {
        $tax1 = new CalculatedTax(19.0, 100.0, 19.0, 'label 1');
        $tax2 = new CalculatedTax(19.0, 100.0, 19.0);

        $tax = $tax1->add($tax2);

        static::assertSame(38.0, $tax->tax);
        static::assertSame(100.0, $tax->taxRate);
        static::assertSame(38.0, $tax->price);
        static::assertSame('label 1', $tax->label);
    }

    public function testJsonSerialize(): void
    {
        $tax = new CalculatedTax(19.0, 100.0, 19.0, 'label');

        static::assertSame(['tax' => 19.0, 'taxRate' => 100.0, 'price' => 19.0, 'label' => 'label'], $tax->jsonSerialize());
    }
}
