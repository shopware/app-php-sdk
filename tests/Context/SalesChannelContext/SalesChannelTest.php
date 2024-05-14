<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannel;

#[CoversClass(SalesChannel::class)]
class SalesChannelTest extends TestCase
{
    public function testConstruct(): void
    {
        $salesChannel = new SalesChannel([
            'id' => 'test',
            'name' => 'foo',
            'accessKey' => 'eyFoo',
            'taxCalculationType' => 'vertical',
            'currency' => ['id' => 'currency-id'],
            'domains' => [
                ['url' => 'https://foo.com'],
                ['url' => 'https://bar.com'],
            ],
        ]);

        static::assertSame('test', $salesChannel->getId());
        static::assertSame('foo', $salesChannel->getName());
        static::assertSame('eyFoo', $salesChannel->getAccessKey());
        static::assertSame('vertical', $salesChannel->getTaxCalculationType());
        static::assertSame('currency-id', $salesChannel->getCurrency()->getId());
        static::assertCount(2, $salesChannel->getDomains());

        $domains = $salesChannel->getDomains();

        static::assertSame('https://foo.com', $domains->first()?->getUrl());
        static::assertSame('https://bar.com', $domains->last()?->getUrl());
    }
}
