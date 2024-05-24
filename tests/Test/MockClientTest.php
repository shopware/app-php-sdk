<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Test;

use Nyholm\Psr7\Request;
use Nyholm\Psr7\Response;
use PHPUnit\Framework\Attributes\CoversClass;
use RuntimeException;
use Shopware\App\SDK\Test\MockClient;
use PHPUnit\Framework\TestCase;

#[CoversClass(MockClient::class)]
class MockClientTest extends TestCase
{
    public function testEmptyQueueThrowsException(): void
    {
        $client = new MockClient([]);

        static::expectException(RuntimeException::class);
        static::expectExceptionMessage('No more responses available');

        $client->sendRequest(new Request('GET', 'https://example.com'));
    }

    public function testUsesQueue(): void
    {
        $client = new MockClient([
            new Response(200, [], '{"foo": "bar"}'),
            new Response(200, [], '{"baz": "qux"}'),
        ]);

        $response = $client->sendRequest(new Request('GET', 'https://example.com'));
        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"foo": "bar"}', $response->getBody()->getContents());

        $response = $client->sendRequest(new Request('GET', 'https://example.com'));
        static::assertSame(200, $response->getStatusCode());
        static::assertSame('{"baz": "qux"}', $response->getBody()->getContents());
    }

    public function testIsEmpty(): void
    {
        $client = new MockClient([]);

        static::assertTrue($client->isEmpty());
    }

    public function testIsNotEmpty(): void
    {
        $client = new MockClient([
            new Response(200, [], '{"foo": "bar"}'),
        ]);

        static::assertFalse($client->isEmpty());
    }
}
