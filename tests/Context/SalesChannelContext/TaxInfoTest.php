<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\TaxInfo;

#[CoversClass(TaxInfo::class)]
class TaxInfoTest extends TestCase
{
    public function testConstruct(): void
    {
        $taxInfo = new TaxInfo([
            'enabled' => true,
            'currencyId' => 'currency-id',
            'amount' => 100.0,
        ]);

        static::assertTrue($taxInfo->isEnabled());
        static::assertSame('currency-id', $taxInfo->getCurrencyId());
        static::assertSame(100.0, $taxInfo->getAmount());
    }
}
