<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\PaymentMethod;

#[CoversClass(PaymentMethod::class)]
class PaymentMethodTest extends TestCase
{
    public function testConstruct(): void
    {
        $paymentMethod = new PaymentMethod([
            'id' => 'test',
            'name' => 'foo',
            'technicalName' => 'payment_foo',
            'description' => 'FOO',
            'active' => true,
            'afterOrderEnabled' => true,
            'availabilityRuleId' => 'rule-id',
            'synchronous' => true,
            'asynchronous' => true,
            'prepared' => true,
            'refundable' => true,
        ]);

        static::assertSame('test', $paymentMethod->getId());
        static::assertSame('foo', $paymentMethod->getName());
        static::assertSame('payment_foo', $paymentMethod->getTechnicalName());
        static::assertSame('FOO', $paymentMethod->getDescription());
        static::assertTrue($paymentMethod->isActive());
        static::assertSame('rule-id', $paymentMethod->getAvailabilityRuleId());
        static::assertTrue($paymentMethod->isAfterOrderEnabled());
        static::assertTrue($paymentMethod->isSynchronous());
        static::assertTrue($paymentMethod->isAsynchronous());
        static::assertTrue($paymentMethod->isPrepared());
        static::assertTrue($paymentMethod->isRefundable());
    }

    public function testConstructNullable(): void
    {
        $paymentMethod = new PaymentMethod([
            'availabilityRuleId' => null,
        ]);

        static::assertNull($paymentMethod->getAvailabilityRuleId());
    }
}
