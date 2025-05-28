<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Context\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Gateway\Context\Command\AddCustomerMessageCommand;

#[CoversClass(AddCustomerMessageCommand::class)]
class AddCustomerMessageCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new AddCustomerMessageCommand('foo');

        static::assertSame('foo', $command->message);
        static::assertSame('foo', $command->getPayloadValue('message'));
    }

    public function testKey(): void
    {
        static::assertSame('context_add-customer-message', AddCustomerMessageCommand::KEY);
    }
}
