<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\SalesChannelContext\Address;
use PHPUnit\Framework\TestCase;

#[CoversClass(Address::class)]
#[CoversClass(ArrayStruct::class)]
class AddressTest extends TestCase
{
    public function testEmptySalutation(): void
    {
        $address = new Address([]);

        static::assertNull($address->getSalutation());
        static::assertNull($address->getCountryState());
        static::assertSame([], $address->toArray());
    }

    public function testCompanyFilled(): void
    {
        $address = new Address([
            'company' => 'test',
            'department' => 'test',
            'title' => 'test',
            'phoneNumber' => 'test',
            'additionalAddressLine1' => 'test',
            'additionalAddressLine2' => 'test',
        ]);

        static::assertSame('test', $address->getCompany());
        static::assertSame('test', $address->getDepartment());
        static::assertSame('test', $address->getTitle());
        static::assertSame('test', $address->getPhoneNumber());
        static::assertSame('test', $address->getAdditionalAddressLine1());
        static::assertSame('test', $address->getAdditionalAddressLine2());
    }
}
