<?php

namespace Amp\Injector\Meta\ParameterAttribute;

use Amp\Injector\Arguments;
use Amp\Injector\Meta\ParameterAttribute;

interface Factory extends ParameterAttribute
{
    /**
     * @param class-string $class
     * @param callable(string): string|null $alias
     * @param Arguments|null $arguments
     * @return mixed
     */
    public function createDefinition(string $class, \Closure $alias, ?Arguments $arguments = null);
}
