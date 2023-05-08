<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Exception;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Exception\SignatureNotFoundException;
use PHPUnit\Framework\TestCase;

#[CoversClass(SignatureNotFoundException::class)]
class SignatureNotFoundExceptionTest extends TestCase
{
    public function testException(): void
    {
        $request = new Request('GET', 'http://localhost');
        $exception = new SignatureNotFoundException($request);

        static::assertSame($request, $exception->getRequest());
        static::assertSame('Signature is not present in request', $exception->getMessage());
        static::assertSame(0, $exception->getCode());
    }
}
