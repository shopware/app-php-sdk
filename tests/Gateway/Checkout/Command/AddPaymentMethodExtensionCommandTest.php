<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Checkout\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Gateway\Checkout\Command\AddPaymentMethodExtensionCommand;

#[CoversClass(AddPaymentMethodExtensionCommand::class)]
class AddPaymentMethodExtensionCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new AddPaymentMethodExtensionCommand('paypal', 'foo', ['bar']);

        static::assertSame('paypal', $command->paymentMethodTechnicalName);
        static::assertSame('foo', $command->extensionKey);
        static::assertSame(['bar'], $command->extensionsPayload);
        static::assertSame('add-payment-method-extension', $command->keyName);
    }

    public function testPayloadOnConstruct(): void
    {
        $command = new AddPaymentMethodExtensionCommand('paypal', 'foo', ['bar']);

        static::assertSame('paypal', $command->getPayloadValue('paymentMethodTechnicalName'));
        static::assertSame('foo', $command->getPayloadValue('extensionKey'));
        static::assertSame(['bar'], $command->getPayloadValue('extensionsPayload'));
    }

    public function testKey(): void
    {
        static::assertSame('add-payment-method-extension', AddPaymentMethodExtensionCommand::KEY);
    }
}
