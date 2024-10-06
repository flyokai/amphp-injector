<?php

namespace Amp\Injector\Meta\ParameterAttribute;

use Amp\Injector\AliasResolver;
use Amp\Injector\Definition;
use Amp\Injector\InjectionException;
use Attribute;
use Amp\Injector\Arguments;
use function Amp\Injector\object;
use function Amp\Injector\singleton;
use function Amp\Injector\proxy;

#[Attribute(Attribute::TARGET_PARAMETER)]
class SharedProxy extends PrivateProxy implements ProxyParameter
{
    public function createDefinition(string $class, \Closure $alias, ?Arguments $arguments = null): Definition
    {
        /** @var class-string $__class */
        $__class = $alias($this->class)??$this->class;
        return singleton(proxy($class, object($__class, $arguments)));
    }
}
