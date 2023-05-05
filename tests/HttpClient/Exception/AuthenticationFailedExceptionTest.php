<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\HttpClient\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Message\ResponseInterface;
use Shopware\App\SDK\HttpClient\Exception\AuthenticationFailedException;
use PHPUnit\Framework\TestCase;

#[CoversClass(AuthenticationFailedException::class)]
class AuthenticationFailedExceptionTest extends TestCase
{
    public function testException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $exception = new AuthenticationFailedException('shop-id', $response);

        static::assertSame('Authentication failed for shop shop-id', $exception->getMessage());
        static::assertSame($response, $exception->getResponse());
        static::assertSame(0, $exception->getCode());
    }
}
