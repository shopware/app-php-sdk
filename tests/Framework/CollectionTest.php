<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Tests\Framework;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Shopware\App\SDK\Context\Cart\CalculatedTax;
use Shopware\App\SDK\Framework\Collection;

#[CoversClass(Collection::class)]
class CollectionTest extends TestCase
{
    public function testConstruct(): void
    {
        $collection = new Collection([1, 2, 3]);

        static::assertEquals([1, 2, 3], $collection->all());
    }

    public function testAll(): void
    {
        $collection = new Collection(['1', 2.3, 'foo']);

        static::assertEquals(['1', 2.3, 'foo'], $collection->all());
    }

    public function testAdd(): void
    {
        $collection = new Collection();

        $collection->add('foo');
        $collection->add('bar');
        $collection->add('baz');

        static::assertEquals(['foo', 'bar', 'baz'], $collection->all());
    }

    public function testSet(): void
    {
        $collection = new Collection();

        $collection->set(0, 'foo');
        $collection->set(1, 'bar');
        $collection->set(0, 'baz');
        $collection->set('foo', 'bar');

        static::assertEquals(['baz', 'bar', 'foo' => 'bar'], $collection->all());
    }

    public function testGet(): void
    {
        $collection = new Collection(['foo', 'bar', 'baz' => 'qux']);

        static::assertEquals('bar', $collection->get(1));
        static::assertEquals('qux', $collection->get('baz'));

        static::assertEquals(null, $collection->get(3));
        static::assertEquals(null, $collection->get('invalid'));
    }

    public function testFirst(): void
    {
        $collection = new Collection(['foo', 'bar', 'baz' => 'qux']);

        static::assertEquals('foo', $collection->first());
    }

    public function testLast(): void
    {
        $collection = new Collection(['foo', 'bar', 'baz' => 'qux']);

        static::assertEquals('qux', $collection->last());
    }

    public function testRemove(): void
    {
        $collection = new Collection(['foo', 'bar', 'baz' => 'qux']);

        $collection->remove(0);
        $collection->remove('baz');

        $collection->remove(3);
        $collection->remove('invalid');

        static::assertEquals([1 => 'bar'], $collection->all());
    }

    public function testHas(): void
    {
        $collection = new Collection(['foo', 'bar', 'baz' => 'qux']);

        static::assertTrue($collection->has(0));
        static::assertTrue($collection->has(1));
        static::assertTrue($collection->has('baz'));

        static::assertFalse($collection->has(3));
        static::assertFalse($collection->has('invalid'));
    }

    public function testMap(): void
    {
        $collection = new Collection(['foo', 'bar', 'baz' => 'qux']);

        $new = $collection->map(function (string $value) {
            return $value . '_new';
        });

        static::assertEquals(['foo_new', 'bar_new', 'baz' => 'qux_new'], $new);
    }

    public function testFilter(): void
    {
        $collection = new Collection(['foo', 'bar', 'baz' => 'qux']);

        $new = $collection->filter(function (string $value) {
            return $value === 'bar';
        });

        static::assertEquals([1 => 'bar'], $new->all());
    }

    public function testGetIterator(): void
    {
        $collection = new Collection(['foo', 'bar', 'baz' => 'qux']);

        $result = [];
        foreach ($collection as $key => $value) {
            $result[$key] = $value;
        }

        static::assertEquals(['foo', 'bar', 'baz' => 'qux'], $result);
    }

    public function testCount(): void
    {
        $collection = new Collection(['foo', 'bar', 'baz' => 'qux']);

        static::assertEquals(3, $collection->count());
    }

    public function testJsonSerialize(): void
    {
        $jsonSerialize = new class () implements \JsonSerializable {
            public CalculatedTax $tax;

            public function __construct()
            {
                $this->tax = new CalculatedTax([
                    'taxRate' => 19,
                    'tax' => 200,
                    'price' => 100,
                ]);
            }

            /**
             * @return mixed[]
             */
            public function jsonSerialize(): array
            {
                return $this->tax->jsonSerialize();
            }
        };

        $collection = new Collection(['foo', 'bar', 'baz' => 'qux', $jsonSerialize, $jsonSerialize]);

        static::assertEquals([
            'foo',
            'bar',
            'baz' => 'qux',
            ['taxRate' => 19, 'tax' => 200, 'price' => 100],
            ['taxRate' => 19, 'tax' => 200, 'price' => 100],
        ], $collection->jsonSerialize());
    }
}
