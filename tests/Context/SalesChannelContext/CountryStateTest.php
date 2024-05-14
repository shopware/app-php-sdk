<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\CountryState;

#[CoversClass(CountryState::class)]
class CountryStateTest extends TestCase
{
    public function testConstruct(): void
    {
        $countryState = new CountryState([
            'id' => 'test',
            'name' => 'foo',
            'shortCode' => 'FOO',
            'position' => 1,
        ]);

        static::assertSame('test', $countryState->getId());
        static::assertSame('foo', $countryState->getName());
        static::assertSame('FOO', $countryState->getShortCode());
        static::assertSame(1, $countryState->getPosition());
    }
}
