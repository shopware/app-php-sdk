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
        $source = new ActionSource('source-type', 'source-uri');

        static::assertSame('source-type', $source->url);
        static::assertSame('source-uri', $source->appVersion);
    }
}
