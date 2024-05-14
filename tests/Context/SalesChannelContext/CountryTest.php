<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\Country;

#[CoversClass(Country::class)]
class CountryTest extends TestCase
{
    public function testConstruct(): void
    {
        $country = new Country([
            'name' => 'test',
            'iso' => 'foo',
            'iso3' => 'FOO',
            'customerTax' => ['currencyId' => 'currency-id'],
            'companyTax' => ['currencyId' => 'currency-id'],
        ]);

        static::assertSame('test', $country->getName());
        static::assertSame('foo', $country->getIso());
        static::assertSame('FOO', $country->getIso3());
        static::assertSame('currency-id', $country->getCustomerTax()->getCurrencyId());
        static::assertSame('currency-id', $country->getCompanyTax()->getCurrencyId());
    }
}
