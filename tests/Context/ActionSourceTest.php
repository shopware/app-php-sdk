<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;
use Shopware\App\SDK\Framework\Collection;

#[CoversClass(ActionSource::class)]
class ActionSourceTest extends TestCase
{
    public function testConstructDefaults(): void
    {
        $url = 'https://example.com';
        $version = '1.0.0';

        $source = new ActionSource($url, $version, new Collection());

        static::assertSame($url, $source->url);
        static::assertSame($version, $source->appVersion);
        static::assertEquals(new Collection(), $source->inAppPurchases);
        static::assertNull($source->shopwareVersion);
        static::assertNull($source->userLanguage);
    }

    public function testConstruct(): void
    {
        $url = 'https://example.com';
        $version = '1.0.0';

        $source = new ActionSource($url, $version, new Collection(), '6.7.0.0', 'en-GB');

        static::assertSame($url, $source->url);
        static::assertSame($version, $source->appVersion);
        static::assertEquals(new Collection(), $source->inAppPurchases);
        static::assertSame('6.7.0.0', $source->shopwareVersion);
        static::assertSame('en-GB', $source->userLanguage);
    }
}
