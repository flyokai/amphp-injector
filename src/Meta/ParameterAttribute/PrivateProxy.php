<?php

namespace Amp\Injector\Meta\ParameterAttribute;

use Attribute;
use Amp\Injector\Arguments;
use function Amp\Injector\object;
use function Amp\Injector\proxy;

#[Attribute(Attribute::TARGET_PARAMETER)]
class PrivateProxy implements Factory, ProxyParameter
{
    public function __construct(
        readonly public string $class
    ) {
    }
    public function createDefinition(string $class, \Closure $alias, ?Arguments $arguments = null)
    {
        $__class = $alias($this->class)??$this->class;
        return proxy($class, object($__class, $arguments));
    }
}
