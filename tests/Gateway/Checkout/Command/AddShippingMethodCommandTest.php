<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Checkout\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Gateway\Checkout\Command\AddShippingMethodCommand;

#[CoversClass(AddShippingMethodCommand::class)]
class AddShippingMethodCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new AddShippingMethodCommand('dhl');

        static::assertSame('dhl', $command->shippingMethodTechnicalName);
        static::assertSame('add-shipping-method', $command->keyName);
    }

    public function testPayloadOnConstruct(): void
    {
        $command = new AddShippingMethodCommand('dhl');

        static::assertSame('dhl', $command->getPayloadValue('shippingMethodTechnicalName'));
    }

    public function testKey(): void
    {
        static::assertSame('add-shipping-method', AddShippingMethodCommand::KEY);
    }
}
