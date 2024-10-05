<?php

namespace Amp\Injector\Composition;

use Traversable;

/**
 * @template T
 */
interface Composition extends \IteratorAggregate
{
    /**
     * @return Traversable<T>
     */
    public function getIterator(): Traversable;
    public static function selfFactory(): \Closure;
}
