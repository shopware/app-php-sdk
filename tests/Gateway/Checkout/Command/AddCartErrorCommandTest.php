<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Checkout\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\Error;
use Shopware\App\SDK\Gateway\Checkout\Command\AddCartErrorCommand;

#[CoversClass(AddCartErrorCommand::class)]
class AddCartErrorCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new AddCartErrorCommand('foo', true, Error::LEVEL_ERROR);

        static::assertSame('foo', $command->message);
        static::assertTrue($command->blocking);
        static::assertSame(Error::LEVEL_ERROR, $command->level);
        static::assertSame('add-cart-error', $command->keyName);
    }

    public function testConstructWithDefaults(): void
    {
        $command = new AddCartErrorCommand('foo');

        static::assertSame('foo', $command->message);
        static::assertFalse($command->blocking);
        static::assertSame(Error::LEVEL_WARNING, $command->level);
        static::assertSame('add-cart-error', $command->keyName);
    }

    public function testPayloadOnConstruct(): void
    {
        $command = new AddCartErrorCommand('foo', true, Error::LEVEL_ERROR);

        static::assertSame('foo', $command->getPayloadValue('message'));
        static::assertTrue($command->getPayloadValue('blocking'));
        static::assertSame(Error::LEVEL_ERROR, $command->getPayloadValue('level'));
    }

    public function testKey(): void
    {
        static::assertSame('add-cart-error', AddCartErrorCommand::KEY);
    }
}
