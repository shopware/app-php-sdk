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
            'id' => 'payment-method-id',
            'name' => 'name',
            'description' => 'description',
            'active' => true,
            'afterOrderEnabled' => true,
            'availabilityRuleId' => 'availability-rule-id',
            'synchronous' => true,
            'asynchronous' => true,
            'prepared' => true,
            'refundable' => true,
        ]);

        static::assertSame('payment-method-id', $paymentMethod->getId());
        static::assertSame('name', $paymentMethod->getName());
        static::assertSame('description', $paymentMethod->getDescription());
        static::assertTrue($paymentMethod->isActive());
        static::assertTrue($paymentMethod->isAfterOrderEnabled());
        static::assertSame('availability-rule-id', $paymentMethod->getAvailabilityRuleId());
        static::assertTrue($paymentMethod->isSynchronous());
        static::assertTrue($paymentMethod->isAsynchronous());
        static::assertTrue($paymentMethod->isPrepared());
        static::assertTrue($paymentMethod->isRefundable());
    }

    public function testNullables(): void
    {
        $paymentMethod = new PaymentMethod([
            'availabilityRuleId' => null,
        ]);

        static::assertNull($paymentMethod->getAvailabilityRuleId());
    }
}
