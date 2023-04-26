<?php

namespace Shopware\App\SDK\Tests\Exception;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Exception\SignatureInvalidException;
use PHPUnit\Framework\TestCase;

#[CoversClass(SignatureInvalidException::class)]
class SignatureInvalidExceptionTest extends TestCase
{
    public function testException(): void
    {
        $request = new Request('GET', 'http://localhost');
        $exception = new SignatureInvalidException($request);

        static::assertSame($request, $exception->getRequest());
        static::assertSame('Signature could not be verified', $exception->getMessage());
    }
}
