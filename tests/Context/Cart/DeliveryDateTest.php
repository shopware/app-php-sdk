<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\DeliveryDate;

#[CoversClass(DeliveryDate::class)]
class DeliveryDateTest extends TestCase
{
    public function testConstruct(): void
    {
        $deliveryDate = [
            'earliest' => '2021-01-01T00:00:00+00:00',
            'latest' => '2021-01-01T00:00:00+00:00',
        ];

        $deliveryDate = new DeliveryDate($deliveryDate);

        static::assertSame('2021-01-01T00:00:00+00:00', $deliveryDate->getEarliest()->format(\DateTimeInterface::ATOM));
        static::assertSame('2021-01-01T00:00:00+00:00', $deliveryDate->getLatest()->format(\DateTimeInterface::ATOM));
    }
}
