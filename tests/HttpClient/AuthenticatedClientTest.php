<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\HttpClient;

use Nyholm\Psr7\Factory\Psr17Factory;
use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\SimpleCache\CacheInterface;
use Shopware\App\SDK\HttpClient\AuthenticatedClient;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\HttpClient\Exception\AuthenticationFailedException;
use Shopware\App\SDK\HttpClient\NullCache;
use Shopware\App\SDK\Test\MockClient;
use Shopware\App\SDK\Test\MockShop;

#[CoversClass(AuthenticatedClient::class)]
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

    public function testCacheSet(): void
    {
        $mockClient = new MockClient([
            new Response(200, [], '{"access_token": "access-token", "expires_in": 3600}'),
            new Response(200, [], '{"foo": "bar"}'),
        ]);

        $cache = static::createMock(CacheInterface::class);
        $cache
            ->expects(static::once())
            ->method('set')
            ->with('shop-id-access-token', 'access-token', 3570);

        $client = $this->getAuthenticatedClient($mockClient, $cache);
        $client->sendRequest(new Request('GET', 'https://example.com'));
    }

    public function testAuthorizationHeaderSet(): void
    {
        $request = static::createMock(RequestInterface::class);
        $request
            ->expects(static::once())
            ->method('withHeader')
            ->with('Authorization', 'Bearer access-token')
            ->willReturn($request);

        $mockClient = new MockClient([
            new Response(200, [], '{"access_token": "access-token", "expires_in": 3600}'),
            new Response(200, [], '{"foo": "bar"}'),
        ]);

        $client = $this->getAuthenticatedClient($mockClient);
        $client->sendRequest($request);
    }

    public function testCreateTokenRequest(): void
    {
        $shop = new MockShop('shop-id', 'shop-secret', 'shop-url', true, 'shop-id', 'shop-secret');

        $factory = new Psr17Factory();
        $request = $factory->createRequest('POST', sprintf('%s/api/oauth/token', $shop->getShopUrl()));

        $request = $request
            ->withHeader('Content-Type', 'application/json')
            ->withBody($factory->createStream(json_encode([
                'grant_type' => 'client_credentials',
                'client_id' => $shop->getShopClientId(),
                'client_secret' => $shop->getShopClientSecret(),
            ], JSON_THROW_ON_ERROR)));

        $mockClient = static::createMock(ClientInterface::class);
        $mockClient
            ->expects(static::exactly(2))
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($shop): ResponseInterface {
                $body = \json_decode($request->getBody()->getContents(), true);

                static::assertIsArray($body);

                if (\array_key_exists('grant_type', $body)) {
                    static::assertArrayHasKey('client_id', $body);
                    static::assertArrayHasKey('client_secret', $body);

                    static::assertSame('shop-secret/api/oauth/token', $request->getUri()->getPath());
                    static::assertSame('application/json', $request->getHeaderLine('Content-Type'));
                    static::assertSame('client_credentials', $body['grant_type']);
                    static::assertSame('shop-id', $body['client_id']);
                    static::assertSame('shop-secret', $body['client_secret']);

                    return new Response(200, [], '{"access_token": "access-token", "expires_in": 3600}');
                }

                return new Response();
            });

        $client = new AuthenticatedClient($mockClient, $shop, new NullCache());
        $client->sendRequest($request);
    }

    public function getAuthenticatedClient(MockClient $mockClient, CacheInterface $cache = new NullCache()): AuthenticatedClient
    {
        return new AuthenticatedClient(
            $mockClient,
            new MockShop('shop-id', 'shop-secret', 'shop-url'),
            $cache
        );
    }

    public function testInvalidJsonTokenResponseThrowsException(): void
    {
        $mockClient = new MockClient([
            new Response(200, [], 'not-a-json'),
        ]);

        $client = $this->getAuthenticatedClient($mockClient);

        static::expectException(AuthenticationFailedException::class);
        $client->sendRequest(new Request('GET', 'https://example.com'));
    }

    public function testMissingTokenFieldsThrowsException(): void
    {
        $mockClient = new MockClient([
            new Response(200, [], '{"foo":"bar"}'),
        ]);

        $client = $this->getAuthenticatedClient($mockClient);
        static::expectException(AuthenticationFailedException::class);

        $client->sendRequest(new Request('GET', 'https://example.com'));
    }
}
