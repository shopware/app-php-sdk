<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Checkout\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Gateway\Checkout\Command\AddPaymentMethodCommand;

#[CoversClass(AddPaymentMethodCommand::class)]
class AddPaymentMethodCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new AddPaymentMethodCommand('paypal');

        static::assertSame('paypal', $command->paymentMethodTechnicalName);
        static::assertSame('add-payment-method', $command->keyName);
    }

    public function testPayloadOnConstruct(): void
    {
        $command = new AddPaymentMethodCommand('paypal');

        static::assertSame('paypal', $command->getPayloadValue('paymentMethodTechnicalName'));
    }

    public function testKey(): void
    {
        static::assertSame('add-payment-method', AddPaymentMethodCommand::KEY);
    }
}
