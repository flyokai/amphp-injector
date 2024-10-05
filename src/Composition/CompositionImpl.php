<?php

namespace Amp\Injector\Composition;

use ArrayIterator;

class CompositionImpl extends \ArrayObject implements Composition
{
    public static function selfFactory(int $flags = 0, string $iteratorClass = ArrayIterator::class): \Closure
    {
        static $cache = [];
        $key = sprintf('%d-%s-%s', $flags, $iteratorClass, static::class);
        if (!isset($cache[$key])) {
            $cache[$key] = static function (...$args) use ($flags, $iteratorClass) {
                return new static($args, $flags, $iteratorClass);
            };
            $cache[$key] = $cache[$key]->bindTo(null, static::class);
        }
        return $cache[$key];
    }

}
