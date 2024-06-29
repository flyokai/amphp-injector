<?php

namespace Amp\Injector\Meta\ParameterAttribute;

use Amp\Injector\AliasResolver;
use Attribute;
use Amp\Injector\Arguments;
use function Amp\Injector\object;
use function Amp\Injector\singleton;

#[Attribute(Attribute::TARGET_PARAMETER)]
class SharedParameter implements Factory
{
    public function createDefinition(string $class, \Closure $alias, ?Arguments $arguments = null)
    {
        $__class = $alias($class)??$class;
        return singleton(object($__class, $arguments));
    }
}
