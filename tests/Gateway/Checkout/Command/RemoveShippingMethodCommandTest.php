<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Checkout\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Gateway\Checkout\Command\RemoveShippingMethodCommand;

#[CoversClass(RemoveShippingMethodCommand::class)]
class RemoveShippingMethodCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new RemoveShippingMethodCommand('dhl');

        static::assertSame('dhl', $command->shippingMethodTechnicalName);
        static::assertSame('remove-shipping-method', $command->keyName);
    }

    public function testPayloadOnConstruct(): void
    {
        $command = new RemoveShippingMethodCommand('dhl');

        static::assertSame('dhl', $command->getPayloadValue('shippingMethodTechnicalName'));
    }

    public function testKey(): void
    {
        static::assertSame('remove-shipping-method', RemoveShippingMethodCommand::KEY);
    }
}
