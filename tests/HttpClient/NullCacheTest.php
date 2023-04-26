<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\HttpClient;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\HttpClient\NullCache;
use PHPUnit\Framework\TestCase;

#[CoversClass(NullCache::class)]
class NullCacheTest extends TestCase
{
    public function testCaching(): void
    {
        $cache = new NullCache();

        static::assertTrue($cache->clear());
        static::assertTrue($cache->set('foo', 1));
        static::assertSame([], $cache->getMultiple(['foo']));
        static::assertFalse($cache->has('foo'));
        static::assertNull($cache->get('foo'));
        static::assertTrue($cache->delete('foo'));
        static::assertTrue($cache->deleteMultiple(['foo']));
        static::assertTrue($cache->setMultiple(['foo']));
    }
}
