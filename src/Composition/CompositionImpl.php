<?php

namespace Amp\Injector\Composition;

use ArrayIterator;

/**
 * @implements Composition<string|int, mixed>
 * @extends \ArrayObject<string|int, mixed>
 */
class CompositionImpl extends \ArrayObject implements Composition
{
    /**
     * @param int $flags
     * @param class-string<ArrayIterator<int|string, mixed>> $iteratorClass
     * @return \Closure(mixed ...$args): Composition<string|int, mixed>
     */
    public static function selfFactory(int $flags = 0, string $iteratorClass = ArrayIterator::class): \Closure
    {
        static $cache = [];
        $key = sprintf('%d-%s-%s', $flags, $iteratorClass, static::class);
        if (!isset($cache[$key])) {
            $cache[$key] = static function (mixed ...$args) use ($flags, $iteratorClass) {
                // @phpstan-ignore-next-line
                return new static($args, $flags, $iteratorClass);
            };
            $cache[$key] = $cache[$key]->bindTo(null, static::class);
        }
        return $cache[$key];
    }

}
