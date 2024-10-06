<?php

namespace Amp\Injector\Meta\ParameterAttribute;

use Amp\Injector\Definition;
use Attribute;
use Amp\Injector\Arguments;
use function Amp\Injector\object;

#[Attribute(Attribute::TARGET_PARAMETER)]
class PrivateParameter implements Factory
{
    public function createDefinition(string $class, \Closure $alias, ?Arguments $arguments = null): Definition
    {
        /** @var class-string $__class */
        $__class = $alias($class)??$class;
        return object($__class, $arguments);
    }
}
