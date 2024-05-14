<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Context\SalesChannelContext\Customer;
use PHPUnit\Framework\TestCase;

#[CoversClass(Customer::class)]
class CustomerTest extends TestCase
{
    public function testConstruct(): void
    {
        $customer = new Customer([
            'id' => 'test',
            'firstName' => 'test',
            'lastName' => 'test',
            'email' => 'test@example.com',
            'company' => 'test ltd.',
            'customerNumber' => '123',
            'title' => 'Dr.',
            'active' => true,
            'guest' => false,
            'accountType' => 'customer',
            'vatIds' => ['foo', 'bar'],
            'remoteAddress' => '127.0.0.1',
            'salutation' => ['id' => 'test'],
            'defaultPaymentMethod' => ['id' => 'test'],
            'defaultBillingAddress' => ['id' => 'test'],
            'defaultShippingAddress' => ['id' => 'test'],
            'activeBillingAddress' => ['id' => 'test'],
            'activeShippingAddress' => ['id' => 'test'],
        ]);

        static::assertSame('test', $customer->getId());
        static::assertSame('test', $customer->getFirstName());
        static::assertSame('test', $customer->getLastName());
        static::assertSame('test@example.com', $customer->getEmail());
        static::assertSame('test ltd.', $customer->getCompany());
        static::assertSame('123', $customer->getCustomerNumber());
        static::assertSame('Dr.', $customer->getTitle());
        static::assertTrue($customer->isActive());
        static::assertFalse($customer->isGuest());
        static::assertSame('customer', $customer->getAccountType());
        static::assertSame(['foo', 'bar'], $customer->getVatIds());
        static::assertSame('127.0.0.1', $customer->getRemoteAddress());
        static::assertSame('test', $customer->getSalutation()?->getId());
        static::assertSame('test', $customer->getDefaultPaymentMethod()->getId());
        static::assertSame('test', $customer->getDefaultBillingAddress()->getId());
        static::assertSame('test', $customer->getDefaultShippingAddress()->getId());
        static::assertSame('test', $customer->getActiveBillingAddress()->getId());
        static::assertSame('test', $customer->getActiveShippingAddress()->getId());
    }

    public function testConstructNullables(): void
    {
        $customer = new Customer([
            'company' => null,
            'title' => null,
            'salutation' => null,
        ]);

        static::assertNull($customer->getCompany());
        static::assertNull($customer->getTitle());
        static::assertNull($customer->getSalutation());
    }
}
