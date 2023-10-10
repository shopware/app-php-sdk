<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Context\SalesChannelContext\Address;
use Shopware\App\SDK\Context\SalesChannelContext\Customer;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\PaymentMethod;
use Shopware\App\SDK\Context\SalesChannelContext\Salutation;

#[CoversClass(Customer::class)]
class CustomerTest extends TestCase
{
    public function testConstruct(): void
    {
        $salutation = ['id' => 'foo'];
        $paymentMethod = ['id' => 'bar'];
        $address = ['id' => 'baz'];

        $customer = new Customer([
            'id' => 'customer-id',
            'firstName' => 'first-name',
            'lastName' => 'last-name',
            'email' => 'email',
            'company' => 'company',
            'customerNumber' => 'customer-number',
            'title' => 'title',
            'active' => true,
            'guest' => true,
            'accountType' => 'account-type',
            'vatIds' => ['foo', 'bar'],
            'remoteAddress' => 'remote-address',
            'salutation' => $salutation,
            'defaultPaymentMethod' => $paymentMethod,
            'defaultBillingAddress' => $address,
            'defaultShippingAddress' => $address,
            'activeBillingAddress' => $address,
            'activeShippingAddress' => $address,
        ]);

        static::assertSame('customer-id', $customer->getId());
        static::assertSame('first-name', $customer->getFirstName());
        static::assertSame('last-name', $customer->getLastName());
        static::assertSame('email', $customer->getEmail());
        static::assertSame('company', $customer->getCompany());
        static::assertSame('customer-number', $customer->getCustomerNumber());
        static::assertSame('title', $customer->getTitle());
        static::assertTrue($customer->isActive());
        static::assertTrue($customer->isGuest());
        static::assertSame('account-type', $customer->getAccountType());
        static::assertSame(['foo', 'bar'], $customer->getVatIds());
        static::assertSame('remote-address', $customer->getRemoteAddress());
        static::assertEquals(new Salutation($salutation), $customer->getSalutation());
        static::assertEquals(new PaymentMethod($paymentMethod), $customer->getDefaultPaymentMethod());
        static::assertEquals(new Address($address), $customer->getDefaultBillingAddress());
        static::assertEquals(new Address($address), $customer->getDefaultShippingAddress());
        static::assertEquals(new Address($address), $customer->getActiveBillingAddress());
        static::assertEquals(new Address($address), $customer->getActiveShippingAddress());
    }

    public function testNullables(): void
    {
        $customer = new Customer([
            'company' => null,
            'title' => null,
            'vatIds' => null,
            'salutation' => null,
        ]);

        static::assertNull($customer->getCompany());
        static::assertNull($customer->getTitle());
        static::assertSame([], $customer->getVatIds());
        static::assertNull($customer->getSalutation());
    }
}
