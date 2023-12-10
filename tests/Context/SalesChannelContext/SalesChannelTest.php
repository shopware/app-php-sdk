<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\Currency;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannel;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelDomain;

#[CoversClass(SalesChannel::class)]
class SalesChannelTest extends TestCase
{
    public function testConstruct(): void
    {
        $currency = ['id' => 'foo'];
        $salesChannelDomains = [
            ['id' => 'foo'],
            ['id' => 'bar'],
        ];

        $salesChannel = new SalesChannel([
            'id' => 'sales-channel-id',
            'name' => 'name',
            'accessKey' => 'accessKey',
            'taxCalculationType' => 'taxCalculationType',
            'currency' => $currency,
            'domains' => $salesChannelDomains,
        ]);

        static::assertSame('sales-channel-id', $salesChannel->getId());
        static::assertSame('name', $salesChannel->getName());
        static::assertSame('accessKey', $salesChannel->getAccessKey());
        static::assertSame('taxCalculationType', $salesChannel->getTaxCalculationType());
        static::assertEquals(new Currency($currency), $salesChannel->getCurrency());
        static::assertEquals([new SalesChannelDomain($salesChannelDomains[0]), new SalesChannelDomain($salesChannelDomains[1])], $salesChannel->getDomains());
    }
}
