<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Checkout\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Gateway\Checkout\Command\RemovePaymentMethodCommand;

#[CoversClass(RemovePaymentMethodCommand::class)]
class RemovePaymentMethodCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new RemovePaymentMethodCommand('paypal');

        static::assertSame('paypal', $command->paymentMethodTechnicalName);
        static::assertSame('remove-payment-method', $command->keyName);
    }

    public function testPayloadOnConstruct(): void
    {
        $command = new RemovePaymentMethodCommand('paypal');

        static::assertSame('paypal', $command->getPayloadValue('paymentMethodTechnicalName'));
    }

    public function testKey(): void
    {
        static::assertSame('remove-payment-method', RemovePaymentMethodCommand::KEY);
    }
}
