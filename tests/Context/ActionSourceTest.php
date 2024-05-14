<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ActionSource;

#[CoversClass(ActionSource::class)]
class ActionSourceTest extends TestCase
{
    public function testConstruct(): void
    {
        $url = 'https://example.com';
        $version = '1.0.0';

        $source = new ActionSource($url, $version);

        static::assertSame($url, $source->url);
        static::assertSame($version, $source->appVersion);
    }
}
