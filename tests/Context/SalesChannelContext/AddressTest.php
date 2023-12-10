<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Context\SalesChannelContext\Address;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\Country;
use Shopware\App\SDK\Context\SalesChannelContext\CountryState;
use Shopware\App\SDK\Context\SalesChannelContext\Salutation;

#[CoversClass(Address::class)]
class AddressTest extends TestCase
{
    public function testConstruct(): void
    {
        $salutation = [
            'id' => 'foo',
            'displayName' => 'bar',
            'letterName' => 'baz.',
            'salutationKey' => 'bar'
        ];

        $country = [
            'name' => 'foo',
            'iso' => 'bar',
            'iso3' => 'baz',
            'customerTax' => [
                'enabled' => true,
                'currencyId' => 'currency-id',
                'amount' => 1.0,
            ],
            'companyTax' => [
                'enabled' => true,
                'currencyId' => 'currency-id',
                'amount' => 1.0,
            ],
        ];

        $countryState = [
            'id' => 'country-state-id',
            'name' => 'foo',
            'shortCode' => 'bar',
            'position' => 1,
        ];

        $address = new Address([
            'id' => 'id',
            'salutation' => $salutation,
            'firstName' => 'first-name',
            'lastName' => 'last-name',
            'street' => 'street',
            'zipcode' => 'zipcode',
            'city' => 'city',
            'company' => 'company',
            'department' => 'department',
            'title' => 'title',
            'country' => $country,
            'countryState' => $countryState,
            'phoneNumber' => 'phone-number',
            'additionalAddressLine1' => 'additional-address-line-1',
            'additionalAddressLine2' => 'additional-address-line-2',
        ]);

        static::assertSame('id', $address->getId());
        static::assertEquals(new Salutation($salutation), $address->getSalutation());
        static::assertSame('first-name', $address->getFirstName());
        static::assertSame('last-name', $address->getLastName());
        static::assertSame('street', $address->getStreet());
        static::assertSame('zipcode', $address->getZipCode());
        static::assertSame('city', $address->getCity());
        static::assertSame('company', $address->getCompany());
        static::assertSame('department', $address->getDepartment());
        static::assertSame('title', $address->getTitle());
        static::assertEquals(new Country($country), $address->getCountry());
        static::assertEquals(new CountryState($countryState), $address->getCountryState());
        static::assertSame('phone-number', $address->getPhoneNumber());
        static::assertSame('additional-address-line-1', $address->getAdditionalAddressLine1());
        static::assertSame('additional-address-line-2', $address->getAdditionalAddressLine2());
    }

    public function testEmptySalutation(): void
    {
        $address = new Address([]);

        static::assertNull($address->getSalutation());
        static::assertNull($address->getCompany());
        static::assertNull($address->getDepartment());
        static::assertNull($address->getTitle());
        static::assertNull($address->getPhoneNumber());
        static::assertNull($address->getCountryState());
        static::assertNull($address->getAdditionalAddressLine1());
        static::assertNull($address->getAdditionalAddressLine2());
        static::assertSame([], $address->toArray());
    }
}
