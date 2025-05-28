<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Context\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Gateway\Context\Command\ChangeLanguageCommand;

#[CoversClass(ChangeLanguageCommand::class)]
class ChangeLanguageCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new ChangeLanguageCommand('de-DE');

        static::assertSame('de-DE', $command->iso);
        static::assertSame('de-DE', $command->getPayloadValue('iso'));
        static::assertSame('context_change-language', $command->keyName);
    }

    public function testKey(): void
    {
        static::assertSame('context_change-language', ChangeLanguageCommand::KEY);
    }
}
