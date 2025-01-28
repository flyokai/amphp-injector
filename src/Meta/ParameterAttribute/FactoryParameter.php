<?php

namespace Amp\Injector\Meta\ParameterAttribute;

use Amp\Injector\Definition;
use Amp\Injector\InjectionException;
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

    /**
     * @param \Closure(string): (string|null) $alias
     * @param Arguments|null $arguments
     * @return Definition
     * @throws InjectionException
     */
    public function createDefinition(\Closure $alias, ?Arguments $arguments = null): Definition
    {
        /** @var class-string $class */
        $class = $alias($this->class)??$this->class;
        $__cb = static function (mixed ...$args) use ($class) {
            return new $class(...$args);
        };
        return injectableFactory($class, $__cb, $arguments);
    }
}
