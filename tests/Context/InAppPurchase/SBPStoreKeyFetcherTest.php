<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\InAppPurchase;

use Nyholm\Psr7\Factory\Psr17Factory;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Client\ClientInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;
use Shopware\App\SDK\Context\InAppPurchase\SBPStoreKeyFetcher;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Test\JWKSHelper;
use Shopware\App\SDK\Test\MockClient;
use Strobotti\JWK\KeySet;

#[CoversClass(SBPStoreKeyFetcher::class)]
class SBPStoreKeyFetcherTest extends TestCase
{
    public function testGetKeyFromSBP(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects(static::once())
            ->method('set')
            ->with(
                SBPStoreKeyFetcher::SBP_JWT_CACHE_KEY,
                JWKSHelper::getPublicJWKS()
            )
            ->willReturn(true);

        $cache
            ->expects(static::once())
            ->method('get')
            ->with(SBPStoreKeyFetcher::SBP_JWT_CACHE_KEY)
            ->willReturn(JWKSHelper::getPublicJWKS());

        $factory = new Psr17Factory();
        $response = $factory->createResponse();
        $stream = $factory->createStream(JWKSHelper::getPublicJWKS());
        $response = $response->withBody($stream);

        $client = new MockClient([$response]);
        $fetcher = new SBPStoreKeyFetcher($client, $cache);

        $keys = $fetcher->getKey(true);

        static::assertNotNull($keys);
        static::assertSame(JWKSHelper::getStaticKid(), $keys->getKeys()[0]->getKeyId());
    }

    public function testCacheIsPreferred(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects(static::once())
            ->method('get')
            ->with(SBPStoreKeyFetcher::SBP_JWT_CACHE_KEY)
            ->willReturn(JWKSHelper::getPublicJWKS());

        $cache
            ->expects(static::never())
            ->method('set');

        $client = $this->createMock(ClientInterface::class);
        $client
            ->expects(static::never())
            ->method('sendRequest');

        $fetcher = new SBPStoreKeyFetcher($client, $cache);

        $keys = $fetcher->getKey();

        static::assertNotNull($keys);
        static::assertSame(JWKSHelper::getStaticKid(), $keys->getKeys()[0]->getKeyId());
    }

    public function testEmptySetIsReturnedOnCacheMisHit(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects(static::once())
            ->method('get')
            ->with(SBPStoreKeyFetcher::SBP_JWT_CACHE_KEY)
            ->willReturn(null);

        $fetcher = new SBPStoreKeyFetcher(new MockClient([]), $cache);

        $keys = $fetcher->getKey();

        static::assertEquals(new KeySet(), $keys);
    }

    public function testCacheFailIsLogged(): void
    {
        $cache = $this->createMock(CacheInterface::class);
        $cache
            ->expects(static::once())
            ->method('set')
            ->willReturn(false);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('error')
            ->with('Unable to fetch a JWKS token from SBP: The JWKS was not stored in the cache successfully.');

        $factory = new Psr17Factory();
        $response = $factory->createResponse();
        $stream = $factory->createStream(JWKSHelper::getPublicJWKS());
        $response = $response->withBody($stream);

        $fetcher = new SBPStoreKeyFetcher(new MockClient([$response]), $cache, $logger);

        $keys = $fetcher->getKey(true);

        static::assertEquals(new KeySet(), $keys);
    }

    public function testSBPRequestFailsIsLogged(): void
    {
        $cache = $this->createMock(CacheInterface::class);

        $logger = $this->createMock(LoggerInterface::class);
        $logger
            ->expects(static::once())
            ->method('error')
            ->with('Unable to fetch a JWKS token from SBP: Request to the SBP failed.');

        $factory = new Psr17Factory();
        $response = $factory->createResponse(400);
        $stream = $factory->createStream(JWKSHelper::getPublicJWKS());
        $response = $response->withBody($stream);

        $fetcher = new SBPStoreKeyFetcher(new MockClient([$response]), $cache, $logger);

        $keys = $fetcher->getKey(true);

        static::assertEquals(new KeySet(), $keys);
    }
}
