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
        $raw = new \Nyholm\Psr7\Response(200, ['Content-Type' => 'application/json'], '{"foo": "bar", "baz": 1}');
        $response = new Response($raw);

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('application/json', $response->getHeader('Content-Type'));
        static::assertSame('{"foo": "bar", "baz": 1}', $response->getContent());
        static::assertSame(['foo' => 'bar', 'baz' => 1], $response->toArray());
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
    /**
     * @dataProvider okDataProvider
     */
    public function testOk(int $status, bool $shouldBeOk): void
    {
        $raw = new \Nyholm\Psr7\Response($status, ['Content-Type' => 'application/json'], 'true');
        $response = new Response($raw);

        static::assertSame($response->ok(), $shouldBeOk);
    }

    /**
     * @return iterable<array{int, bool}>
     */
    public static function okDataProvider(): iterable
    {
        yield [200, true];
        yield [201, true];
        yield [299, true];
        yield [199, false];
        yield [300, false];
        yield [400, false];
        yield [500, false];
        yield [-1, false];
    }
}
