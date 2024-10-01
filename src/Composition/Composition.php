<?php

namespace Amp\Injector\Composition;

interface Composition extends \IteratorAggregate
{
    static public function selfFactory(): \Closure;
}
