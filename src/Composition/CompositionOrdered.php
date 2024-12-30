<?php

namespace Amp\Injector\Composition;

use ArrayIterator;
use MJS\TopSort\CircularDependencyException;
use MJS\TopSort\ElementNotFoundException;
use MJS\TopSort\Implementations\StringSort;

class CompositionOrdered extends CompositionImpl
{
    const FLAG_KEEP_KEYS = 2**10;
    protected int $origFlags = 0;
    protected array $sortedCompositionItems = [];
    /**
     * @param object|array<string, CompositionItem> $array
     * @param int $flags
     * @param class-string<ArrayIterator<int|string, mixed>> $iteratorClass
     */
    public function __construct(
        object|array $array = [], int $flags = 0, string $iteratorClass = "ArrayIterator", ?\Closure $sorter=null
    ) {
        $this->origFlags = $flags;
        $isKeepKeys = self::hasKeepKeysFlag($flags);
        if ($sorter === null) {
            $sorter = self::topSort(...);
        }
        $this->sortedCompositionItems = $sorted = [];
        $__sorted = $sorter($array, $flags);
        foreach ($__sorted as $key=>$item) {
            if ($isKeepKeys) {
                if ($item instanceof CompositionItem) {
                    $sorted[$key] = $item->value;
                    $this->sortedCompositionItems[$key] = $item;
                } else {
                    $sorted[$key] = $item;
                }
            } else {
                if ($item instanceof CompositionItem) {
                    $sorted[] = $item->value;
                    $this->sortedCompositionItems[spl_object_id($item->value)] = $item;
                } else {
                    $sorted[] = $item;
                }
            }
        }
        $flags = self::removeMyFlags($flags);
        parent::__construct($sorted, $flags, $iteratorClass);
    }

    public static function removeMyFlags(int $flags=0): int
    {
        $flags = $flags & ~self::FLAG_KEEP_KEYS;
        return $flags;
    }

    public static function hasKeepKeysFlag(int $flags): bool
    {
        return (bool)($flags & self::FLAG_KEEP_KEYS);
    }

    /**
     * @param int $flags
     * @param class-string<ArrayIterator<int|string, mixed>> $iteratorClass
     * @param \Closure|null $sorter
     * @return \Closure(mixed ...$args): Composition<int|string, mixed>
     */
    public static function selfFactory(
        int $flags = 0, string $iteratorClass = ArrayIterator::class, ?\Closure $sorter=null
    ): \Closure
    {
        static $cache = [];
        if ($sorter === null) {
            $sorter = self::defaultSorter();
        }
        $key = sprintf('%d-%d-%s-%s', spl_object_id($sorter), $flags, $iteratorClass, static::class);
        if (!isset($cache[$key])) {
            $cache[$key] = static function (...$args) use ($sorter, $flags, $iteratorClass) {
                // @phpstan-ignore-next-line
                return new static($args, $flags, $iteratorClass, $sorter);
            };
            $cache[$key] = $cache[$key]->bindTo(null, static::class);
        }
        return $cache[$key];
    }

    /**
     * @return \Closure(array<string, CompositionItem>): (CompositionItem[])
     */
    public static function defaultSorter(): \Closure
    {
        static $defaultSorter;
        return $defaultSorter ??= self::topSort(...);
    }

    /**
     * @param array<string, CompositionItem> $array
     * @return CompositionItem[]
     * @throws CircularDependencyException
     * @throws ElementNotFoundException
     */
    public static function topSort(array $array, ?int $flags=null): array
    {
        $isKeepKeys = self::hasKeepKeysFlag($flags);
        foreach ($array as $name => $item) {
            if (!is_string($name)) {
                throw new \InvalidArgumentException(sprintf(
                    '%s $array argument keys must be string', self::class
                ));
            }
            if (!$item instanceof CompositionItem) {
                throw new \InvalidArgumentException(sprintf(
                    '%s array argument values must be instance of %s',
                    self::class, CompositionItem::class
                ));
            }
            foreach ($item->before as $before) {
                if (isset($array[$before])) {
                    $array[$before] = $array[$before]->withDepends([$name]);
                }
            }
            $array[$name] = $array[$name]->withDepends($item->after);
        }
        $sort = new StringSort;
        foreach ($array as $name => $item) {
            $sort->add($name, $item->depends());
        }
        $sortedNames = array_filter($sort->sort());
        $sorted = [];
        foreach ($sortedNames as $name) {
            if ($isKeepKeys) {
                $sorted[$name] = $array[$name];
            } else {
                $sorted[] = $array[$name];
            }
        }
        return $sorted;
    }
}
