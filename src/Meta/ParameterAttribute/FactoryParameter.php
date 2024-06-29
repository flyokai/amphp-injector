<?php

namespace Amp\Injector\Meta\ParameterAttribute;

use Attribute;
use Amp\Injector\Arguments;
use function Amp\Injector\injectableFactory;

#[Attribute(Attribute::TARGET_PARAMETER)]
class FactoryParameter implements Service
{
    public function __construct(
        readonly public string $class
    ) {
    }

    public function createDefinition(string $class, \Closure $alias, ?Arguments $arguments = null)
    {
        $class = $alias($this->class);
        $__cb = static function (...$args) use ($class, $alias) {
            return new $class(...$args);
        };
        return injectableFactory($__cb, $class, $arguments);
    }
}
