<?php

namespace Amp\Injector\Meta\ParameterAttribute;

use Attribute;
use Amp\Injector\Arguments;
use function Amp\Injector\object;
use function Amp\Injector\singleton;
use function Amp\Injector\proxy;

#[Attribute(Attribute::TARGET_PARAMETER)]
class SharedProxy extends PrivateProxy implements ProxyParameter
{
    public function createDefinition(string $class, ?Arguments $arguments = null)
    {
        return singleton(proxy($class, object($this->class, $arguments)));
    }
}