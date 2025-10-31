<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\SalesChannelContext;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\SalesChannelContext\LanguageInfo;

#[CoversClass(LanguageInfo::class)]
class LanguageInfoTest extends TestCase
{
    public function testConstruct(): void
    {
        $languageInfo = new LanguageInfo([
            'name' => 'English',
            'localeCode' => 'en-GB',
        ]);

        static::assertSame('English', $languageInfo->getName());
        static::assertSame('en-GB', $languageInfo->getLocaleCode());
    }
}
