<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\HttpClient;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\SimpleCache\CacheInterface;
use Shopware\App\SDK\HttpClient\AuthenticatedClient;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\HttpClient\Exception\AuthenticationFailedException;
use Shopware\App\SDK\HttpClient\NullCache;
use Shopware\App\SDK\Test\MockClient;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(AuthenticatedClient::class)]
#[CoversClass(MockShop::class)]
#[CoversClass(NullCache::class)]
#[CoversClass(AuthenticationFailedException::class)]
#[CoversClass(MockClient::class)]
class AuthenticatedClientTest extends TestCase
{
    public function testAuthenticationFails(): void
    {
        $mockClient = new MockClient([
            new Response(401),
        ]);

        $client = $this->getAuthenticatedClient($mockClient);

        static::expectException(AuthenticationFailedException::class);
        $client->sendRequest(new Request('GET', 'https://example.com'));
    }

    public function testAuthenticationWorks(): void
    {
        $mockClient = new MockClient([
            new Response(200, [], '{"access_token": "access-token", "expires_in": 3600}'),
            new Response(200, [], '{"foo": "bar"}'),
        ]);

        $response = $this->getAuthenticatedClient($mockClient)
            ->sendRequest(new Request('GET', 'https://example.com'));

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"foo": "bar"}', $response->getBody()->getContents());
        static::assertTrue($mockClient->isEmpty());
    }

    public function testAuthenticationCacheInvalidIgnored(): void
    {
        $mockClient = new MockClient([
            new Response(200, [], '{"access_token": "access-token", "expires_in": 3600}'),
            new Response(200, [], '{"foo": "bar"}'),
        ]);

        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')
            ->willReturn([]);

        $response = $this->getAuthenticatedClient($mockClient, $cache)
            ->sendRequest(new Request('GET', 'https://example.com'));

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"foo": "bar"}', $response->getBody()->getContents());
    }

    public function testAuthenticationTokenCached(): void
    {
        $mockClient = new MockClient([
            new Response(200, [], '{"foo": "bar"}'),
        ]);

        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')
            ->with('shop-id-access-token')
            ->willReturn('asdass');

        $response = $this->getAuthenticatedClient($mockClient, $cache)
            ->sendRequest(new Request('GET', 'https://example.com'));

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"foo": "bar"}', $response->getBody()->getContents());
    }

    public function getAuthenticatedClient(MockClient $mockClient, CacheInterface $cache = new NullCache()): AuthenticatedClient
    {
        return new AuthenticatedClient(
            $mockClient,
            new MockShop('shop-id', 'shop-secret', 'shop-url'),
            $cache
        );
    }
}
