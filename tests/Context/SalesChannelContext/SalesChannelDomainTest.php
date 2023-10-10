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
        $domain = new SalesChannelDomain([
            'id' => 'domain-id',
            'url' => 'https://example.com',
            'languageId' => 'language-id',
            'currencyId' => 'currency-id',
            'snippetSetId' => 'snippet-set-id',
        ]);

        static::assertSame('domain-id', $domain->getId());
        static::assertSame('https://example.com', $domain->getUrl());
        static::assertSame('language-id', $domain->getLanguageId());
        static::assertSame('currency-id', $domain->getCurrencyId());
        static::assertSame('snippet-set-id', $domain->getSnippetSetId());
    }
}
