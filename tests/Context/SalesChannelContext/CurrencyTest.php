<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\SalesChannelContext\Currency;

#[CoversClass(Currency::class)]
#[CoversClass(ArrayStruct::class)]
class CurrencyTest extends TestCase
{
    public function testGetTaxFreeFrom(): void
    {
        $currency = new Currency(['taxFreeFrom' => 100]);
        static::assertSame(100.0, $currency->getTaxFreeFrom());
        static::assertIsFloat($currency->getTaxFreeFrom());
    }
}
