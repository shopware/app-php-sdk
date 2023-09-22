<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Order;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\Order\OrderCustomer;
use Shopware\App\SDK\Context\SalesChannelContext\Customer;
use Shopware\App\SDK\Context\SalesChannelContext\Salutation;

#[CoversClass(OrderCustomer::class)]
#[UsesClass(ArrayStruct::class)]
#[UsesClass(Customer::class)]
#[UsesClass(Salutation::class)]
class OrderCustomerTest extends TestCase
{
    public function testGetId(): void
    {
        $customer = new OrderCustomer(['id' => 'foo']);
        static::assertSame('foo', $customer->getId());
    }

    public function testGetEmail(): void
    {
        $customer = new OrderCustomer(['email' => 'foo']);
        static::assertSame('foo', $customer->getEmail());
    }

    public function testGetFirstName(): void
    {
        $customer = new OrderCustomer(['firstName' => 'foo']);
        static::assertSame('foo', $customer->getFirstName());
    }

    public function testGetLastName(): void
    {
        $customer = new OrderCustomer(['lastName' => 'foo']);
        static::assertSame('foo', $customer->getLastName());
    }

    public function testGetTitle(): void
    {
        $customer = new OrderCustomer(['title' => 'foo']);
        static::assertSame('foo', $customer->getTitle());
    }

    public function testGetVatIds(): void
    {
        $customer = new OrderCustomer(['vatIds' => ['foo', 'bar']]);
        static::assertSame(['foo', 'bar'], $customer->getVatIds());
    }

    public function testGetCompany(): void
    {
        $customer = new OrderCustomer(['company' => 'foo']);
        static::assertSame('foo', $customer->getCompany());
    }

    public function testGetCustomerNumber(): void
    {
        $customer = new OrderCustomer(['customerNumber' => 'foo']);
        static::assertSame('foo', $customer->getCustomerNumber());
    }

    public function testGetSalutation(): void
    {
        $customer = new OrderCustomer(['salutation' => ['id' => 'foo']]);
        $salutation = $customer->getSalutation();

        static::assertSame('foo', $customer->getSalutation()->getId());
    }

    public function testGetCustomer(): void
    {
        $customer = new OrderCustomer(['customer' => ['id' => 'foo']]);
        static::assertSame('foo', $customer->getCustomer()->getId());
    }

    public function testGetRemoteAddress(): void
    {
        $customer = new OrderCustomer(['remoteAddress' => 'foo']);
        static::assertSame('foo', $customer->getRemoteAddress());
    }
}
