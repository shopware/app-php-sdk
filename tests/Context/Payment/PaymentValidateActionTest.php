<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Payment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Cart\Cart;
use Shopware\App\SDK\Context\InAppPurchase\InAppPurchase;
use Shopware\App\SDK\Context\Payment\PaymentValidateAction;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;
use Shopware\App\SDK\Framework\Collection;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(PaymentValidateAction::class)]
class PaymentValidateActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $shop = new MockShop('shop-id', 'https://shop-url.com', 'shop-secret');
        $IAPs = new Collection([new InAppPurchase('id', 1)]);
        $source = new ActionSource('https://shop-url.com', '1.0.0', $IAPs);
        $cart = new Cart(['token' => 'cart-token']);
        $salesChannelContext = new SalesChannelContext(['token' => 'context-token']);
        $requestData = ['foo' => 'bar'];

        $action = new PaymentValidateAction($shop, $source, $cart, $salesChannelContext, $requestData);

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame($cart, $action->cart);
        static::assertSame($salesChannelContext, $action->salesChannelContext);
        static::assertSame($requestData, $action->requestData);
        static::assertSame($IAPs, $action->source->inAppPurchases);
    }
}
