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

    public function testIsset(): void
    {
        $struct = new class ([
            'string' => 'bar',
            'false' => false,
            'zero' => 0,
            'empty-string' => '',
            'empty-array' => [],
            'null' => null,
        ]) extends ArrayStruct {};

        static::assertTrue($struct->isset('string'));
        static::assertTrue($struct->isset('false'));
        static::assertTrue($struct->isset('zero'));
        static::assertTrue($struct->isset('empty-string'));
        static::assertTrue($struct->isset('empty-array'));
        static::assertFalse($struct->isset('null'));
        static::assertFalse($struct->isset('missing'));
    }

    public function testIsNull(): void
    {
        $struct = new class ([
            'string' => 'bar',
            'false' => false,
            'zero' => 0,
            'empty-string' => '',
            'empty-array' => [],
            'null' => null,
        ]) extends ArrayStruct {};

        static::assertFalse($struct->isNull('string'));
        static::assertFalse($struct->isNull('false'));
        static::assertFalse($struct->isNull('zero'));
        static::assertFalse($struct->isNull('empty-string'));
        static::assertFalse($struct->isNull('empty-array'));
        static::assertFalse($struct->isNull('missing'));
        static::assertTrue($struct->isNull('null'));
    }
}
