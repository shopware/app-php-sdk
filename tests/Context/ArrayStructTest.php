<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Context;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\ArrayStruct;

#[CoversClass(ArrayStruct::class)]
class ArrayStructTest extends TestCase
{
    public function testStruct(): void
    {
        $struct = new class (['foo' => 'bar', 'baz' => 'bax']) extends ArrayStruct {};

        static::assertSame(['foo' => 'bar', 'baz' => 'bax'], $struct->toArray());
        static::assertSame(['foo' => 'bar', 'baz' => 'bax'], $struct->jsonSerialize());
    }
}
