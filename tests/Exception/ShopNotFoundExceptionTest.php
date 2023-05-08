<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Exception;

use Nyholm\Psr7\Request;
use PHPUnit\Framework\Attributes\CoversClass;
use Shopware\App\SDK\Exception\ShopNotFoundException;
use PHPUnit\Framework\TestCase;

#[CoversClass(ShopNotFoundException::class)]
class ShopNotFoundExceptionTest extends TestCase
{
    public function testException(): void
    {
        $request = new Request('GET', 'http://localhost');
        $exception = new ShopNotFoundException('foo');

        static::assertSame('Shop with id "foo" not found', $exception->getMessage());
        static::assertSame(0, $exception->getCode());
    }
}
