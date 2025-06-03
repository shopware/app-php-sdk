<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Context;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Gateway\Context\ContextGatewayCommand;

#[CoversClass(ContextGatewayCommand::class)]
class ContextGatewayCommandTest extends TestCase
{
    public function testSetPayloadValue(): void
    {
        $command = $this->getCommand();
        $command->setPayloadValue('key', 'value');

        static::assertSame('value', $command->getPayloadValue('key'));
    }

    public function testHasPayloadValue(): void
    {
        $command = $this->getCommand();
        $command->setPayloadValue('key', 'value');

        static::assertTrue($command->hasPayloadValue('key'));
    }

    public function testNotHasPayloadValue(): void
    {
        $command = $this->getCommand();
        $command->setPayloadValue('key', 'value');

        static::assertFalse($command->hasPayloadValue('foo'));
    }

    public function testGetPayloadValue(): void
    {
        $command = $this->getCommand();
        $command->setPayloadValue('key', 'value');

        static::assertSame('value', $command->getPayloadValue('key'));
    }

    public function testGetNonExistentPayloadValue(): void
    {
        $command = $this->getCommand();
        $command->setPayloadValue('key', 'value');

        static::assertNull($command->getPayloadValue('foo'));
    }

    public function testJsonSerialize(): void
    {
        $command = $this->getCommand('foo');
        $command->setPayloadValue('key', 'value');

        static::assertSame(['command' => 'foo', 'payload' => ['key' => 'value']], $command->jsonSerialize());
        static::assertSame('foo', $command->getKey());
    }

    private function getCommand(string $key = 'key'): ContextGatewayCommand
    {
        return new class ($key) extends ContextGatewayCommand {
            public function __construct(string $key)
            {
                $this->keyName = $key;
            }
        };
    }
}
