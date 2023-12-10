<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\Delivery;
use Shopware\App\SDK\Context\Cart\DeliveryDate;
use Shopware\App\SDK\Context\Cart\DeliveryPosition;
use Shopware\App\SDK\Context\SalesChannelContext\ShippingLocation;
use Shopware\App\SDK\Context\SalesChannelContext\ShippingMethod;

#[CoversClass(Delivery::class)]
class DeliveryTest extends TestCase
{
    public function testConstruct(): void
    {
        $lineItem = [
            'uniqueIdentifier' => 'unique-identifier',
            'type' => 'product',
            'referencedId' => 'referenced-id',
            'label' => 'label',
            'good' => true,
            'quantity' => 1,
            'payload' => [
                'foo' => 'bar',
            ],
            'price' => [
                'unitPrice' => 1.0,
                'totalPrice' => 1.0,
                'calculatedTaxes' => [],
                'taxRules' => [],
            ],
            'states' => [
                'foo' => 'bar',
            ],
            'children' => [
                [
                    'id' => 'foo',
                    'good' => true,
                ],
                [
                    'id' => 'bar',
                    'good' => false,
                ],
            ],
        ];

        $deliveryDate = [
            'earliest' => '2021-01-01T00:00:00+00:00',
            'latest' => '2021-01-01T00:00:00+00:00',
        ];

        $price = [
            'unitPrice' => 1.0,
            'totalPrice' => 1.0,
            'calculatedTaxes' => [],
            'taxRules' => [],
        ];

        $positions = [
            [
                'identifier' => 'identifier-1',
                'lineItem' => $lineItem,
                'quantity' => 1,
                'deliveryDate' => $deliveryDate,
                'price' => $price,
            ],
            [
                'identifier' => 'identifier-2',
                'lineItem' => $lineItem,
                'quantity' => 2,
                'deliveryDate' => $deliveryDate,
                'price' => $price,
            ],
        ];

        $location = [
            'country' => 'DE',
            'state' => 'NRW',
            'city' => 'Cologne',
            'street' => 'Street',
            'zipcode' => '50667',
        ];

        $shippingMethod = [
            'id' => 'shipping-method-id',
            'name' => 'shipping-method-name',
            'description' => 'shipping-method-description',
            'deliveryTime' => 'shipping-method-delivery-time',
        ];

        $delivery = new Delivery([
            'positions' => $positions,
            'location' => $location,
            'shippingMethod' => $shippingMethod,
            'deliveryDate' => $deliveryDate,
        ]);

        static::assertEquals(new DeliveryPosition($positions[0]), $delivery->getPositions()[0]);
        static::assertEquals(new DeliveryPosition($positions[1]), $delivery->getPositions()[1]);
        static::assertEquals(new ShippingLocation($location), $delivery->getLocation());
        static::assertEquals(new ShippingMethod($shippingMethod), $delivery->getShippingMethod());
        static::assertEquals(new DeliveryDate($deliveryDate), $delivery->getDeliveryDate());
    }
}
