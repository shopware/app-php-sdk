<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Checkout\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Gateway\Checkout\Command\AddShippingMethodExtensionCommand;

#[CoversClass(AddShippingMethodExtensionCommand::class)]
class AddShippingMethodExtensionCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new AddShippingMethodExtensionCommand('dhl', 'foo', ['bar']);

        static::assertSame('dhl', $command->shippingMethodTechnicalName);
        static::assertSame('foo', $command->extensionKey);
        static::assertSame(['bar'], $command->extensionsPayload);
        static::assertSame('add-shipping-method-extension', $command->keyName);
    }

    public function testPayloadOnConstruct(): void
    {
        $command = new AddShippingMethodExtensionCommand('dhl', 'foo', ['bar']);

        static::assertSame('dhl', $command->getPayloadValue('shippingMethodTechnicalName'));
        static::assertSame('foo', $command->getPayloadValue('extensionKey'));
        static::assertSame(['bar'], $command->getPayloadValue('extensionsPayload'));
    }

    public function testKey(): void
    {
        static::assertSame('add-shipping-method-extension', AddShippingMethodExtensionCommand::KEY);
    }
}
