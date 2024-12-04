<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Gateway\Checkout;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Cart\Cart;
use Shopware\App\SDK\Context\Gateway\Checkout\CheckoutGatewayAction;
use Shopware\App\SDK\Context\InAppPurchase\InAppPurchase;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(CheckoutGatewayAction::class)]
class CheckoutGatewayActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $shop = new MockShop('foo', 'https://example.com', 'secret');
        $IAPs = new Collection([new InAppPurchase('id', 1)]);
        $source = new ActionSource('https://example.com', '1.0.0', $IAPs);
        $cart = new Cart([]);
        $context = new SalesChannelContext([]);
        $paymentMethods = new Collection(['foo' => 'bar']);
        $shippingMethods = new Collection(['baz' => 'bax']);

        $action = new CheckoutGatewayAction($shop, $source, $cart, $context, $paymentMethods, $shippingMethods);

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame($cart, $action->cart);
        static::assertSame($context, $action->context);
        static::assertSame($paymentMethods, $action->paymentMethods);
        static::assertSame($shippingMethods, $action->shippingMethods);
        static::assertSame($IAPs, $action->source->inAppPurchases);
    }
}
