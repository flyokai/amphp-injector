<?php

namespace Amp\Injector\Composition;

use Traversable;

/**
 * @template TKey
 * @template-covariant TValue
 * @extends \IteratorAggregate<TKey, TValue>
 */
interface Composition extends \IteratorAggregate
{
    /**
     * @return Traversable<TKey, TValue>
     */
    public function getIterator(): Traversable;
    public static function selfFactory(): \Closure;
}
