<?php

namespace Amp\Injector\Composition;

interface Composition extends \IteratorAggregate
{
    static public function selfFactory(): \Closure;
    static public function fromArgs(...$args): static;
}
