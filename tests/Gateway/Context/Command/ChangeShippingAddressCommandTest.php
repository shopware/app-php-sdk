<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Context\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Gateway\Context\Command\ChangeShippingAddressCommand;

#[CoversClass(ChangeShippingAddressCommand::class)]
class ChangeShippingAddressCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new ChangeShippingAddressCommand('foo');

        static::assertSame('foo', $command->addressId);
        static::assertSame('foo', $command->getPayloadValue('addressId'));
        static::assertSame('context_change-shipping-address', $command->keyName);
    }

    public function testKey(): void
    {
        static::assertSame('context_change-shipping-address', ChangeShippingAddressCommand::KEY);
    }
}
