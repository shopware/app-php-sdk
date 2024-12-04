<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\InAppPurchase;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\InAppPurchase\InAppPurchase;

#[CoversClass(InAppPurchase::class)]
class InAppPurchaseTest extends TestCase
{
    public function testConstructWithDefaults(): void
    {
        $inAppPurchase = new InAppPurchase('key', 1);

        static::assertSame('key', $inAppPurchase->key);
        static::assertSame(1, $inAppPurchase->quantity);
        static::assertNull($inAppPurchase->nextBookingDate);
    }

    public function testConstructWithNextBookingDate(): void
    {
        $nextBookingDate = new \DateTime();
        $inAppPurchase = new InAppPurchase('key', 1, $nextBookingDate);

        static::assertSame('key', $inAppPurchase->key);
        static::assertSame(1, $inAppPurchase->quantity);
        static::assertSame($nextBookingDate, $inAppPurchase->nextBookingDate);
    }
}
