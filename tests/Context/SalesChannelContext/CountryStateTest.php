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
            'id' => 'id',
            'name' => 'name',
            'shortCode' => 'shortCode',
            'position' => 1,
        ]);

        static::assertSame('id', $countryState->getId());
        static::assertSame('name', $countryState->getName());
        static::assertSame('shortCode', $countryState->getShortCode());
        static::assertSame(1, $countryState->getPosition());
    }
}
