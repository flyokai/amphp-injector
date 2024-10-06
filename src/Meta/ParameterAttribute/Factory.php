<?php

namespace Amp\Injector\Meta\ParameterAttribute;

use Amp\Injector\Arguments;
use Amp\Injector\Definition;
use Amp\Injector\InjectionException;
use Amp\Injector\Meta\ParameterAttribute;

interface Factory extends ParameterAttribute
{
    /**
     * @param class-string $class
     * @param \Closure(string): (string|null) $alias
     * @param Arguments|null $arguments
     * @return Definition
     * @throws InjectionException
     */
    public function createDefinition(string $class, \Closure $alias, ?Arguments $arguments = null): Definition;
}
