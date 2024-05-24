<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Context\SalesChannelContext\Address;
use PHPUnit\Framework\TestCase;

#[CoversClass(Address::class)]
class AddressTest extends TestCase
{
    public function testConstruct(): void
    {
        $address = new Address([
            'id' => 'test',
            'salutation' => ['id' => 'test'],
            'firstName' => 'test',
            'lastName' => 'test',
            'street' => 'test',
            'zipcode' => 'test',
            'city' => 'test',
            'company' => 'test',
            'department' => 'test',
            'title' => 'test',
            'country' => [
                'iso3' => 'FOO',
            ],
            'phoneNumber' => 'test',
            'additionalAddressLine1' => 'test',
            'additionalAddressLine2' => 'test',
            'countryState' => ['id' => 'test'],
        ]);

        static::assertSame('test', $address->getId());
        static::assertSame('test', $address->getSalutation()?->getId());
        static::assertSame('test', $address->getFirstName());
        static::assertSame('test', $address->getLastName());
        static::assertSame('test', $address->getStreet());
        static::assertSame('test', $address->getZipCode());
        static::assertSame('test', $address->getCity());
        static::assertSame('test', $address->getCompany());
        static::assertSame('test', $address->getDepartment());
        static::assertSame('test', $address->getTitle());
        static::assertSame('FOO', $address->getCountry()->getIso3());
        static::assertSame('test', $address->getPhoneNumber());
        static::assertSame('test', $address->getAdditionalAddressLine1());
        static::assertSame('test', $address->getAdditionalAddressLine2());
        static::assertSame('test', $address->getCountryState()?->getId());
    }

    public function testConstructWithNullables(): void
    {
        $address = new Address([
            'id' => 'test',
            'firstName' => 'test',
            'lastName' => 'test',
            'street' => 'test',
            'zipcode' => 'test',
            'city' => 'test',
        ]);

        static::assertSame('test', $address->getId());
        static::assertNull($address->getSalutation());
        static::assertSame('test', $address->getFirstName());
        static::assertSame('test', $address->getLastName());
        static::assertSame('test', $address->getStreet());
        static::assertSame('test', $address->getZipCode());
        static::assertSame('test', $address->getCity());
        static::assertNull($address->getCompany());
        static::assertNull($address->getDepartment());
        static::assertNull($address->getTitle());
        static::assertNull($address->getPhoneNumber());
        static::assertNull($address->getAdditionalAddressLine1());
        static::assertNull($address->getAdditionalAddressLine2());
        static::assertNull($address->getCountryState());
    }

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
