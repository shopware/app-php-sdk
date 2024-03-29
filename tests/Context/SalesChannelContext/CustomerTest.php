<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\SalesChannelContext\Customer;
use PHPUnit\Framework\TestCase;

#[CoversClass(Customer::class)]
#[CoversClass(ArrayStruct::class)]
class CustomerTest extends TestCase
{
    public function testEmptySalutation(): void
    {
        $customer = new Customer(['salutation' => null]);
        static::assertNull($customer->getSalutation());
    }

    public function testGetVatIds(): void
    {
        $customer = new Customer(['vatIds' => ['foo', 'bar']]);
        static::assertSame(['foo', 'bar'], $customer->getVatIds());
    }

    public function testGetEmail(): void
    {
        $customer = new Customer(['email' => 'foo']);
        static::assertSame('foo', $customer->getEmail());
    }
}
