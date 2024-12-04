<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Storefront;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\InAppPurchase\InAppPurchase;
use Shopware\App\SDK\Context\Storefront\StorefrontAction;
use Shopware\App\SDK\Context\Storefront\StorefrontClaims;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(StorefrontAction::class)]
class StorefrontActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $shop = new MockShop('shop-id', 'https://example.com', 'shop-secret');
        $claims = new StorefrontClaims(['salesChannelId' => 'sales-channel-id']);
        $IAPs = new Collection([new InAppPurchase('id', 1)]);

        $storefrontAction = new StorefrontAction($shop, $claims, $IAPs);

        static::assertSame($shop, $storefrontAction->shop);
        static::assertSame($claims, $storefrontAction->claims);
        static::assertSame($IAPs, $storefrontAction->inAppPurchases);
    }
}
