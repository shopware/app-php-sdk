<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Context\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Gateway\Context\Command\LoginCustomerCommand;

#[CoversClass(LoginCustomerCommand::class)]
class LoginCustomerCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new LoginCustomerCommand('foo@bar.com');

        static::assertSame('foo@bar.com', $command->customerEmail);
        static::assertSame('foo@bar.com', $command->getPayloadValue('customerEmail'));
    }

    public function testKey(): void
    {
        static::assertSame('context_login-customer', LoginCustomerCommand::KEY);
    }
}
