<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\HttpClient\SimpleHttpClient;

use Nyholm\Psr7\Response as Psr7Response;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Shopware\App\SDK\HttpClient\SimpleHttpClient\Response;
use Shopware\App\SDK\HttpClient\SimpleHttpClient\SimpleHttpClient;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Test\MockClient;

#[CoversClass(SimpleHttpClient::class)]
#[CoversClass(Response::class)]
#[CoversClass(MockClient::class)]
class SimpleHttpClientTest extends TestCase
{
    public function testGet(): void
    {
        $client = new MockClient([
            new Psr7Response(200, [], 'Hello World'),
        ]);

        $simpleHttpClient = new SimpleHttpClient($client);
        $resp = $simpleHttpClient->get('https://example.com');

        static::assertTrue($resp->ok());
        static::assertSame('Hello World', $resp->getContent());
    }

    public function testGetJSON(): void
    {
        $client = new MockClient([
            new Psr7Response(200, [], '{"foo": "bar"}'),
        ]);

        $simpleHttpClient = new SimpleHttpClient($client);
        $resp = $simpleHttpClient->get('https://example.com');

        static::assertTrue($resp->ok());
        static::assertSame(['foo' => 'bar'], $resp->toArray());
    }

    #[DataProvider('provideMethods')]
    public function testOtherMethods(string $method): void
    {
        $client = $this->createMock(ClientInterface::class);
        $client
            ->method('sendRequest')
            ->willReturnCallback(function (RequestInterface $request) use ($method) {
                static::assertSame(strtoupper($method), $request->getMethod());
                static::assertSame('https://example.com', (string) $request->getUri());
                static::assertSame('application/json', $request->getHeaderLine('Content-Type'));
                static::assertSame('1', $request->getHeaderLine('custom'));
                static::assertSame('{"foo":"bar"}', $request->getBody()->getContents());

                return new Psr7Response(200, [], 'Hello World');
            });

        $simpleHttpClient = new SimpleHttpClient($client);
        $resp = $simpleHttpClient->$method('https://example.com', ['foo' => 'bar'], ['custom' => '1']);

        static::assertTrue($resp->ok());
        static::assertSame('Hello World', $resp->getContent());
    }

    public static function provideMethods(): \Generator
    {
        yield 'POST' => [
            'post'
        ];

        yield 'PUT' => [
            'put'
        ];

        yield 'PATCH' => [
            'patch'
        ];

        yield 'DELETE' => [
            'delete'
        ];
    }
}
