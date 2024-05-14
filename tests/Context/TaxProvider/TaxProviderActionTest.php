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
        $shop = new MockShop('shop-id', 'https://example.com', 'shop-secret');
        $source = new ActionSource('https://example.com', '1.0.0');
        $context = new SalesChannelContext(['salesChannelId' => 'sales-channel-id']);
        $cart = new Cart(['token' => 'cart-token']);

        $taxProviderAction = new TaxProviderAction($shop, $source, $context, $cart);

        static::assertSame($shop, $taxProviderAction->shop);
        static::assertSame($source, $taxProviderAction->source);
        static::assertSame($context, $taxProviderAction->context);
        static::assertSame($cart, $taxProviderAction->cart);
    }
}
