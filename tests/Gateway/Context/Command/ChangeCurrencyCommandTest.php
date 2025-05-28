<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Context\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Gateway\Context\Command\ChangeCurrencyCommand;

#[CoversClass(ChangeCurrencyCommand::class)]
class ChangeCurrencyCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new ChangeCurrencyCommand('EUR');

        static::assertSame('EUR', $command->iso);
        static::assertSame('EUR', $command->getPayloadValue('iso'));
    }

    public function testKey(): void
    {
        static::assertSame('context_change-currency', ChangeCurrencyCommand::KEY);
    }
}
