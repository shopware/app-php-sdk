<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Exception;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Exception\MissingClaimException;

#[CoversClass(MissingClaimException::class)]
class MissingClaimExceptionTest extends TestCase
{
    public function testConstruct(): void
    {
        $e = new MissingClaimException('foo');
        static::assertSame('Missing claim "foo", did you forgot to add permissions in your app to this?', $e->getMessage());
    }
}
