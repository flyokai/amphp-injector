<?php

namespace Amp\Injector\Composition;

use ArrayIterator;

class CompositionImpl extends \ArrayObject implements Composition
{
    static public function selfFactory(): \Closure
    {
        return (fn (...$args) => static::fromArgs(...$args))->bindTo(null, static::class);
    }
    static public function fromArgs(...$args): static
    {
        return new static($args);
    }

}
