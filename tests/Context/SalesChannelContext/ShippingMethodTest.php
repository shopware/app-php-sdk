<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\ShippingMethod;

#[CoversClass(ShippingMethod::class)]
class ShippingMethodTest extends TestCase
{
    public function testConstruct(): void
    {
        $shippingMethod = new ShippingMethod([
            'id' => 'shipping-method-id',
            'name' => 'shipping-method-name',
            'taxType' => 'net',
        ]);

        static::assertSame('shipping-method-id', $shippingMethod->getId());
        static::assertSame('shipping-method-name', $shippingMethod->getName());
        static::assertSame('net', $shippingMethod->getTaxType());
    }
}
