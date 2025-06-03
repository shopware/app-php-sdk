<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Context\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Response\Customer\CustomerResponseStruct;
use Shopware\App\SDK\Gateway\Context\Command\RegisterCustomerCommand;

#[CoversClass(RegisterCustomerCommand::class)]
class RegisterCustomerCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $data = new CustomerResponseStruct();
        $data->title = 'title';
        $data->firstName = 'firstName';
        $data->lastName = 'lastName';

        $command = new RegisterCustomerCommand($data);

        static::assertSame($data, $command->data);
        static::assertSame($data, $command->getPayloadValue('data'));
    }

    public function testKey(): void
    {
        static::assertSame('context_register-customer', RegisterCustomerCommand::KEY);
    }
}
