<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;

#[CoversClass(SalesChannelContext::class)]
class SalesChannelContextTest extends TestCase
{
    public function testConstruct(): void
    {
        $context = new SalesChannelContext([
            'token' => 'context-token',
            'context' => [
                'currencyId' => 'currency-id',
                'taxState' => 'net',
                'rounding' => ['decimals' => 2],
            ],
            'currency' => [
                'id' => 'currency-id',
            ],
            'shippingMethod' => [
                'id' => 'shipping-method-id',
            ],
            'paymentMethod' => [
                'id' => 'payment-method-id',
            ],
            'salesChannel' => [
                'id' => 'sales-channel-id',
            ],
            'customer' => [
                'id' => 'customer-id',
            ],
        ]);

        static::assertSame('context-token', $context->getToken());
        static::assertSame('currency-id', $context->getCurrencyId());
        static::assertSame('net', $context->getTaxState());
        static::assertSame(2, $context->getRounding()->getDecimals());
        static::assertSame('currency-id', $context->getCurrency()->getId());
        static::assertSame('shipping-method-id', $context->getShippingMethod()->getId());
        static::assertSame('payment-method-id', $context->getPaymentMethod()->getId());
        static::assertSame('sales-channel-id', $context->getSalesChannel()->getId());
        static::assertSame('customer-id', $context->getCustomer()->getId());
    }

    public function testConstructNullable(): void
    {
        $context = new SalesChannelContext([
            'customer' => null,
        ]);

        static::assertNull($context->getCustomer());
    }
}
