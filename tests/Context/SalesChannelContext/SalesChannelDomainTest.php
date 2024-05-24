<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\SalesChannelDomain;

#[CoversClass(SalesChannelDomain::class)]
class SalesChannelDomainTest extends TestCase
{
    public function testConstruct(): void
    {
        $salesChannelDomain = new SalesChannelDomain([
            'id' => 'sales-channel-domain-id',
            'url' => 'https://foo.com',
            'languageId' => 'language-id',
            'currencyId' => 'currency-id',
            'snippetSetId' => 'snippet-set-id',
        ]);

        static::assertSame('sales-channel-domain-id', $salesChannelDomain->getId());
        static::assertSame('https://foo.com', $salesChannelDomain->getUrl());
        static::assertSame('language-id', $salesChannelDomain->getLanguageId());
        static::assertSame('currency-id', $salesChannelDomain->getCurrencyId());
        static::assertSame('snippet-set-id', $salesChannelDomain->getSnippetSetId());
    }
}
