<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\TaxRule;

#[CoversClass(TaxRule::class)]
class TaxRuleTest extends TestCase
{
    public function testConstruct(): void
    {
        $taxRule = new TaxRule([
            'taxRate' => 0.19,
            'percentage' => 19,
        ]);

        static::assertSame(0.19, $taxRule->getTaxRate());
        static::assertSame(19.0, $taxRule->getPercentage());
    }
}
