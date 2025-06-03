<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Context\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Gateway\Context\Command\ChangeShippingMethodCommand;

#[CoversClass(ChangeShippingMethodCommand::class)]
class ChangeShippingMethodCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new ChangeShippingMethodCommand('shipping_technical-name');

        static::assertSame('shipping_technical-name', $command->technicalName);
        static::assertSame('shipping_technical-name', $command->getPayloadValue('technicalName'));
    }

    public function testKey(): void
    {
        static::assertSame('context_change-shipping-method', ChangeShippingMethodCommand::KEY);
    }
}
