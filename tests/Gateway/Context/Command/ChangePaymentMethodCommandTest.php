<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Context\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Gateway\Context\Command\ChangePaymentMethodCommand;

#[CoversClass(ChangePaymentMethodCommand::class)]
class ChangePaymentMethodCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new ChangePaymentMethodCommand('payment_technical-name');

        static::assertSame('payment_technical-name', $command->technicalName);
        static::assertSame('payment_technical-name', $command->getPayloadValue('technicalName'));
        static::assertSame('context_change-payment-method', $command->keyName);
    }

    public function testKey(): void
    {
        static::assertSame('context_change-payment-method', ChangePaymentMethodCommand::KEY);
    }
}
