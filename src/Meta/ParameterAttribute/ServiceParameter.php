<?php

namespace Amp\Injector\Meta\ParameterAttribute;

use Amp\Injector\Definition;
use Amp\Injector\InjectionException;
use Attribute;
use Amp\Injector\Arguments;
use function Amp\Injector\object;
use function Amp\Injector\singleton;

#[Attribute(Attribute::TARGET_PARAMETER)]
class ServiceParameter implements Factory, Service
{
    public function createDefinition(string $class, \Closure $alias, ?Arguments $arguments = null): Definition
    {
        /** @var class-string $__class */
        $__class = $alias($class)??$class;
        return singleton(object($__class, $arguments));
    }
}
