<?php

namespace Amp\Injector\Meta\ParameterAttribute;

use Amp\Injector\Meta\ParameterAttribute;
use Attribute;
use Amp\Injector\Arguments;
use function Amp\Injector\object;
use function Amp\Injector\singleton;

#[Attribute(Attribute::TARGET_PARAMETER)]
class SharedParameter implements Factory
{
    public function createDefinition(string $class, ?Arguments $arguments = null)
    {
        return singleton(object($class, $arguments));
    }
}