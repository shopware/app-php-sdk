<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\TaxProvider;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Cart\Cart;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;
use Shopware\App\SDK\Context\TaxProvider\TaxProviderAction;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(TaxProviderAction::class)]
class TaxProviderActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $shop = new MockShop('shop-id', 'shop-url', 'shop-secret');
        $source = new ActionSource('shop-url', 'appVersion');
        $context = new SalesChannelContext([]);
        $cart = new Cart([]);

        $action = new TaxProviderAction($shop, $source, $context, $cart);

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame($context, $action->context);
        static::assertSame($cart, $action->cart);
    }
}
