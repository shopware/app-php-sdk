<?php

declare(strict_types=1);

namespace Shopware\App\SDK\Framework;

use Countable;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

/**
 * @template TElement of mixed
 *
 * @implements IteratorAggregate<array-key, TElement>
 */
final class Collection implements Countable, IteratorAggregate, JsonSerializable
{
    /**
     * @var array<array-key, TElement>
     */
    protected array $elements = [];

    /**
     * @param iterable<TElement> $elements
     */
    public function __construct(iterable $elements = [])
    {
        /**
         * @var array-key|null $key
         */
        foreach ($elements as $key => $element) {
            $this->set($key, $element);
        }
    }

    /**
     * @return array<array-key, TElement>
     */
    public function all(): array
    {
        return $this->elements;
    }

    /**
     * @param array-key|null $key
     * @param TElement $element
     */
    public function set(int|string|null $key, $element): void
    {
        $key === null ? $this->elements[] = $element : $this->elements[$key] = $element;
    }

    /**
     * @param TElement $element
     */
    public function add($element): void
    {
        $this->elements[] = $element;
    }

    /**
     * @param array-key $key
     *
     * @return TElement|null
     */
    public function get(int|string $key): mixed
    {
        if ($this->has($key)) {
            return $this->elements[$key];
        }

        return null;
    }

    /**
     * @return TElement|null
     */
    public function first(): mixed
    {
        return $this->elements[\array_key_first($this->elements)] ?? null;
    }

    /**
     * @return TElement|null
     */
    public function last(): mixed
    {
        return $this->elements[array_key_last($this->elements)] ?? null;
    }

    /**
     * @param array-key $key
     */
    public function remove(int|string $key): void
    {
        unset($this->elements[$key]);
    }

    /**
     * @param array-key $key
     */
    public function has(int|string $key): bool
    {
        return \array_key_exists($key, $this->elements);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function map(\Closure $closure): array
    {
        return \array_map($closure, $this->elements);
    }

    /**
     * @return self<TElement>
     */
    public function filter(\Closure $closure): self
    {
        return new Collection(\array_filter($this->elements, $closure));
    }

    /**
     * @return Traversable<TElement>
     */
    public function getIterator(): Traversable
    {
        yield from $this->elements;
    }

    public function count(): int
    {
        return \count($this->elements);
    }

    /**
     * @return array<array-key, mixed>
     */
    public function jsonSerialize(): array
    {
        $result = [];

        foreach ($this->elements as $key => $element) {
            if ($element instanceof JsonSerializable) {
                $result[$key] = $element->jsonSerialize();
                continue;
            }

            $result[$key] = $element;
        }

        return $result;
    }
}
