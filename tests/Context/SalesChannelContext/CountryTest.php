<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\Country;
use Shopware\App\SDK\Context\SalesChannelContext\TaxInfo;

#[CoversClass(Country::class)]
class CountryTest extends TestCase
{
    public function testConstruct(): void
    {
        $taxInfo = [
            'enabled' => true,
            'currencyId' => 'currency-id',
            'amount' => 1.0,
        ];

        $country = new Country([
            'name' => 'foo',
            'iso' => 'bar',
            'iso3' => 'baz',
            'customerTax' => $taxInfo,
            'companyTax' => $taxInfo,
        ]);

        static::assertSame('foo', $country->getName());
        static::assertSame('bar', $country->getIso());
        static::assertSame('baz', $country->getIso3());
        static::assertEquals(new TaxInfo($taxInfo), $country->getCustomerTax());
        static::assertEquals(new TaxInfo($taxInfo), $country->getCompanyTax());
    }
}
