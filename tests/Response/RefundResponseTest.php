<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Response;

use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Response\RefundResponse;
use PHPUnit\Framework\TestCase;

#[CoversClass(RefundResponse::class)]
class RefundResponseTest extends TestCase
{
    public function testOpen(): void
    {
        $response = RefundResponse::open();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        static::assertSame('{"status":"reopen"}', $response->getBody()->getContents());
    }

    public function testInProgress(): void
    {
        $response = RefundResponse::inProgress();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        static::assertSame('{"status":"process"}', $response->getBody()->getContents());
    }

    public function testCancelled(): void
    {
        $response = RefundResponse::cancelled();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        static::assertSame('{"status":"cancel"}', $response->getBody()->getContents());
    }

    public function testFailed(): void
    {
        $response = RefundResponse::failed();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        static::assertSame('{"status":"fail"}', $response->getBody()->getContents());
    }

    public function testCompleted(): void
    {
        $response = RefundResponse::completed();

        static::assertSame(200, $response->getStatusCode());
        static::assertSame('application/json', $response->getHeaderLine('Content-Type'));
        static::assertSame('{"status":"complete"}', $response->getBody()->getContents());
    }
}
