<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Context\SalesChannelContext\Address;
use Shopware\App\SDK\Context\SalesChannelContext\Country;
use Shopware\App\SDK\Context\SalesChannelContext\CountryState;
use Shopware\App\SDK\Context\SalesChannelContext\ShippingLocation;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Shopware\App\SDK\Context\SalesChannelContext\ShippingLocation
 */
#[CoversClass(ShippingLocation::class)]
class ShippingLocationTest extends TestCase
{
    public function testConstruct(): void
    {
        $country = ['id' => 'country-id'];
        $countryState = ['id' => 'country-state-id'];
        $address = ['id' => 'address-id'];

        $location = new ShippingLocation([
            'country' => $country,
            'countryState' => $countryState,
            'address' => $address,
        ]);

        static::assertEquals(new Country($country), $location->getCountry());
        static::assertEquals(new CountryState($countryState), $location->getCountryState());
        static::assertEquals(new Address($address), $location->getAddress());
    }

    public function testNullables(): void
    {
        $location = new ShippingLocation([
            'countryState' => null,
        ]);

        static::assertNull($location->getCountryState());
    }
}
