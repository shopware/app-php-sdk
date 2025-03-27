<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\CalculatedTax;
use Shopware\App\SDK\Framework\Collection;

#[CoversClass(CalculatedTax::class)]
class CalculatedTaxTest extends TestCase
{
    public function testConstruct(): void
    {
        $calculatedTax = new CalculatedTax([
            'taxRate' => 19.0,
            'price' => 10.0,
            'tax' => 1.9,
            'label' => 'label',
        ]);

        static::assertSame(19.0, $calculatedTax->getTaxRate());
        static::assertSame(10.0, $calculatedTax->getPrice());
        static::assertSame(1.9, $calculatedTax->getTax());
        static::assertSame('label', $calculatedTax->getLabel());
    }

    public function testSum(): void
    {
        $calculatedTaxes = new Collection([
            new CalculatedTax([
                'taxRate' => 19.0,
                'price' => 10.0,
                'tax' => 1.9,
                'label' => 'label 1',
            ]),
            new CalculatedTax([
                'taxRate' => 19.0,
                'price' => 5.0,
                'tax' => 0.85,
            ]),
            new CalculatedTax([
                'taxRate' => 7.0,
                'price' => 10.0,
                'tax' => 0.7,
            ]),
        ]);

        $sum = CalculatedTax::sum($calculatedTaxes);

        static::assertCount(2, $sum);

        $tax19 = $sum->get('19');
        static::assertNotNull($tax19);
        static::assertSame(19.0, $tax19->getTaxRate());
        static::assertSame(15.0, $tax19->getPrice());
        static::assertSame(2.75, $tax19->getTax());
        static::assertSame('label 1', $tax19->getLabel());

        $tax7 = $sum->get('7');
        static::assertNotNull($tax7);
        static::assertSame(7.0, $tax7->getTaxRate());
        static::assertSame(10.0, $tax7->getPrice());
        static::assertSame(0.7, $tax7->getTax());
        static::assertNull($tax7->getLabel());
    }
}
