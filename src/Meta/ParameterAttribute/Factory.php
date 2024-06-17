<?php

namespace Amp\Injector\Meta\ParameterAttribute;

use Amp\Injector\Arguments;
use Amp\Injector\Meta\ParameterAttribute;

interface Factory extends ParameterAttribute
{
    public function createDefinition(string $class, ?Arguments $arguments = null);
}