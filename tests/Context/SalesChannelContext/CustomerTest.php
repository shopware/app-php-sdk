<?php

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
}

