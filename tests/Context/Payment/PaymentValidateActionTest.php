<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Payment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Cart\Cart;
use Shopware\App\SDK\Context\Payment\PaymentValidateAction;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(PaymentValidateAction::class)]
class PaymentValidateActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $shop = new MockShop('shopId', 'shopUrl', 'shopVersion');
        $source = new ActionSource('url', 'appVersion');
        $cart = new Cart([]);
        $context = new SalesChannelContext([]);
        $requestData = ['foo' => 'bar'];

        $action = new PaymentValidateAction(
            $shop,
            $source,
            $cart,
            $context,
            $requestData
        );

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame($cart, $action->cart);
        static::assertSame($context, $action->salesChannelContext);
        static::assertSame($requestData, $action->requestData);
    }
}
