<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\HttpClient\SimpleHttpClient;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\HttpClient\SimpleHttpClient\Response;
use PHPUnit\Framework\TestCase;

#[CoversClass(Response::class)]
class ResponseTest extends TestCase
{
    public function testResponse(): void
    {
        $raw = new \Nyholm\Psr7\Response(200, ['Content-Type' => 'application/json'], '{"foo": "bar"}');
        $response = new Response($raw);

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('application/json', $response->getHeader('Content-Type'));
        static::assertSame('{"foo": "bar"}', $response->getContent());
        static::assertSame(['foo' => 'bar'], $response->toArray());
        static::assertTrue($response->ok());
        static::assertSame($raw, $response->getRawResponse());
    }

    public function testNonArrayResponse(): void
    {
        $raw = new \Nyholm\Psr7\Response(200, ['Content-Type' => 'application/json'], 'true');
        $response = new Response($raw);

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('application/json', $response->getHeader('Content-Type'));
        static::expectException(\RuntimeException::class);
        static::expectExceptionMessage('Response is not a valid JSON array');
        $response->toArray();
    }
}
