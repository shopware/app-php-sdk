<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Context\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Gateway\Context\Command\ChangeBillingAddressCommand;

#[CoversClass(ChangeBillingAddressCommand::class)]
class ChangeBillingAddressCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new ChangeBillingAddressCommand('foo');

        static::assertSame('foo', $command->addressId);
        static::assertSame('foo', $command->getPayloadValue('addressId'));
        static::assertSame('context_change-billing-address', $command->keyName);
    }

    public function testKey(): void
    {
        static::assertSame('context_change-billing-address', ChangeBillingAddressCommand::KEY);
    }
}
