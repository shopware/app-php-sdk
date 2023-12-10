<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Payment;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Context\Cart\Cart;
use Shopware\App\SDK\Context\Payment\RiskAssessmentAction;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(RiskAssessmentAction::class)]
class RiskAssessmentActionTest extends TestCase
{
    public function testConstruct(): void
    {
        $shop = new MockShop('foo', 'https://foo.bar', 'devsecret');
        $source = new ActionSource('https://foo.bar', '1.0.0');
        $cart = new Cart(['foo' => 'bar']);
        $context = new SalesChannelContext(['foo' => 'bar']);

        $action = new RiskAssessmentAction(
            $shop,
            $source,
            $cart,
            $context,
            ['payment-id-1' => 'payment-handler-1'],
            ['shipping-id-1' => 'shipping-handler-1'],
        );

        static::assertSame($shop, $action->shop);
        static::assertSame($source, $action->source);
        static::assertSame($cart, $action->cart);
        static::assertSame($context, $action->context);
        static::assertSame(['payment-id-1' => 'payment-handler-1'], $action->paymentMethods);
        static::assertSame(['shipping-id-1' => 'shipping-handler-1'], $action->shippingMethods);
    }
}
