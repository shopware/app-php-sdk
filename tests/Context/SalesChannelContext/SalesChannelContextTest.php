<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\Currency;
use Shopware\App\SDK\Context\SalesChannelContext\Customer;
use Shopware\App\SDK\Context\SalesChannelContext\PaymentMethod;
use Shopware\App\SDK\Context\SalesChannelContext\RoundingConfig;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannel;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelContext;
use Shopware\App\SDK\Context\SalesChannelContext\ShippingMethod;

#[CoversClass(SalesChannelContext::class)]
class SalesChannelContextTest extends TestCase
{
    public function testConstruct(): void
    {
        $rounding = [
            'decimals' => 2,
            'interval' => 0.01,
            'roundForNet' => true,
        ];

        $currency = ['id' => 'foo'];
        $shippingMethod = ['id' => 'foo'];
        $paymentMethod = ['id' => 'foo'];
        $salesChannel = ['id' => 'foo'];
        $customer = ['id' => 'foo'];

        $context = new SalesChannelContext([
            'token' => 'token',
            'context' => [
                'currencyId' => 'currency-id',
                'taxState' => 'taxState',
                'rounding' => $rounding,
            ],
            'currency' => $currency,
            'shippingMethod' => $shippingMethod,
            'paymentMethod' => $paymentMethod,
            'salesChannel' => $salesChannel,
            'customer' => $customer,
        ]);

        static::assertSame('token', $context->getToken());
        static::assertSame('currency-id', $context->getCurrencyId());
        static::assertSame('taxState', $context->getTaxState());
        static::assertEquals(new RoundingConfig($rounding), $context->getRounding());
        static::assertEquals(new Currency($currency), $context->getCurrency());
        static::assertEquals(new ShippingMethod($shippingMethod), $context->getShippingMethod());
        static::assertEquals(new PaymentMethod($paymentMethod), $context->getPaymentMethod());
        static::assertEquals(new SalesChannel($salesChannel), $context->getSalesChannel());
        static::assertEquals(new Customer($customer), $context->getCustomer());
    }

    public function testNullables(): void
    {
        $context = new SalesChannelContext([
            'customer' => null,
        ]);

        static::assertNull($context->getCustomer());
    }
}
