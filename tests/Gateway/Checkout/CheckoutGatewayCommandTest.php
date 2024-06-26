<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Checkout;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Gateway\Checkout\CheckoutGatewayCommand;

#[CoversClass(CheckoutGatewayCommand::class)]
class CheckoutGatewayCommandTest extends TestCase
{
    public function testSetPayloadValue(): void
    {
        $command = $this->getCommand();
        $command->setPayloadValue('key', 'value');

        static::assertEquals('value', $command->getPayloadValue('key'));
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

        static::assertEquals('value', $command->getPayloadValue('key'));
    }

    public function testGetNonExistentPayloadValue(): void
    {
        $command = $this->getCommand();
        $command->setPayloadValue('key', 'value');

        static::assertNull($command->getPayloadValue('foo'));
    }

    public function testJsonSerialize(): void
    {
        $command = $this->getCommand();
        $command->keyName = 'key';
        $command->setPayloadValue('key', 'value');

        static::assertEquals(['command' => 'key', 'payload' => ['key' => 'value']], $command->jsonSerialize());
    }

    private function getCommand(): CheckoutGatewayCommand
    {
        return new class () extends CheckoutGatewayCommand {};
    }
}
