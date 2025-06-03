<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Gateway\Context\Command;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Gateway\Context\Command\ChangeShippingLocationCommand;

#[CoversClass(ChangeShippingLocationCommand::class)]
class ChangeShippingLocationCommandTest extends TestCase
{
    public function testConstruct(): void
    {
        $command = new ChangeShippingLocationCommand('DE', 'DE-BY');

        static::assertSame('DE', $command->countryIso);
        static::assertSame('DE-BY', $command->countryStateIso);
        static::assertSame('DE', $command->getPayloadValue('countryIso'));
        static::assertSame('DE-BY', $command->getPayloadValue('countryStateIso'));
    }

    public function testConstructDefaults(): void
    {
        $command = new ChangeShippingLocationCommand();

        static::assertSame(null, $command->countryIso);
        static::assertSame(null, $command->countryStateIso);
        static::assertFalse($command->hasPayloadValue('countryIso'));
        static::assertFalse($command->hasPayloadValue('countryStateIso'));
    }

    public function testKey(): void
    {
        static::assertSame('context_change-shipping-location', ChangeShippingLocationCommand::KEY);
    }
}
