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
        $deliveryDate = new DeliveryDate([
            'earliest' => '2024-01-01T00:00:00+00:00',
            'latest' => '2024-01-02T00:00:00+00:00',
        ]);

        static::assertSame('2024-01-01T00:00:00+00:00', $deliveryDate->getEarliest()->format(\DateTime::ATOM));
        static::assertSame('2024-01-02T00:00:00+00:00', $deliveryDate->getLatest()->format(\DateTime::ATOM));
    }
}
