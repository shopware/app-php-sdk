<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context\Cart;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\Error;

#[CoversClass(Error::class)]
class ErrorTest extends TestCase
{
    public function testConstants(): void
    {
        static::assertSame(0, Error::LEVEL_NOTICE);
        static::assertSame(10, Error::LEVEL_WARNING);
        static::assertSame(20, Error::LEVEL_ERROR);
    }
}
