<?php

declare(strict_types=1);

namespace Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Exception\MissingClaimException;

#[CoversClass(MissingClaimException::class)]
class MissingClaimExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $exception = new MissingClaimException('test-claim');

        static::assertSame('Missing claim "test-claim", did you forgot to add permissions in your app to this?', $exception->getMessage());
    }
}
