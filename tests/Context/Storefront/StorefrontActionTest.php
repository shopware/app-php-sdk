<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Storefront;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Storefront\StorefrontAction;
use Shopware\App\SDK\Context\Storefront\StorefrontClaims;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(StorefrontAction::class)]
class StorefrontActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $shop = new MockShop('shop-id', 'shop-url', 'shop-secret');
        $claims = new StorefrontClaims([]);

        $action = new StorefrontAction($shop, $claims);

        static::assertSame($shop, $action->shop);
        static::assertSame($claims, $action->claims);
    }
}
