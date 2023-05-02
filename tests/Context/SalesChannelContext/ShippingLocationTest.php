<?php

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Context\ArrayStruct;
use Shopware\App\SDK\Context\SalesChannelContext\ShippingLocation;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 *
 * @covers \Shopware\App\SDK\Context\SalesChannelContext\ShippingLocation
 */
#[CoversClass(ShippingLocation::class)]
#[CoversClass(ArrayStruct::class)]
class ShippingLocationTest extends TestCase
{
    public function testFilledState() :void
    {
        $location = new ShippingLocation(['countryState' => []]);

        static::assertNotNull($location->getCountryState());
    }
}

