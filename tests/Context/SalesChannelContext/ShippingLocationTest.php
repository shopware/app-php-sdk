<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Context\SalesChannelContext\ShippingLocation;
use PHPUnit\Framework\TestCase;

#[CoversClass(ShippingLocation::class)]
class ShippingLocationTest extends TestCase
{
    public function testConstruct(): void
    {
        $shippingLocation = new ShippingLocation([
            'country' => ['iso3' => 'FOO'],
            'countryState' => ['id' => 'state-id'],
            'address' => ['id' => 'address-id'],
        ]);

        static::assertSame('FOO', $shippingLocation->getCountry()->getIso3());
        static::assertSame('state-id', $shippingLocation->getCountryState()->getId());
        static::assertSame('address-id', $shippingLocation->getAddress()->getId());
    }

    public function testConstructNullable(): void
    {
        $shippingLocation = new ShippingLocation([
            'countryState' => null,
        ]);

        static::assertNull($shippingLocation->getCountryState());
    }
}
