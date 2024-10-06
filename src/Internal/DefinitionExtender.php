<?php

namespace Amp\Injector\Internal;

use Amp\Injector\Arguments;

trait DefinitionExtender
{
    protected function prependArguments(Arguments $arguments)
    {
        $this->arguments = $arguments;
    }
}
