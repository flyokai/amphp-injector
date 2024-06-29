<?php

namespace Amp\Injector\Definition;

use Amp\Injector\Arguments;
use Amp\Injector\Definition;
use Amp\Injector\Definitions;
use Amp\Injector\Injector;
use Amp\Injector\Meta\Executable;
use Amp\Injector\Meta\Type;
use Amp\Injector\Provider;
use Amp\Injector\Providers;

final class CompositionDefinition implements Definition
{
    private Executable $executable;
    private Definitions $definitions;
    private Arguments $arguments;

    public function __construct(Executable $executable, Definitions $definitions, Arguments $arguments)
    {
        $this->executable = $executable;
        $this->definitions = $definitions;
        $this->arguments = $arguments;
    }

    public function getType(): ?Type
    {
        return $this->executable->getType();
    }

    public function getAttribute(string $attribute): ?object
    {
        return $this->executable->getAttribute($attribute);
    }

    public function build(Injector $injector): Provider
    {
        $providers = new Providers();
        foreach ($this->definitions as $id=>$definition) {
            $providers = $providers->with($definition->build($injector), $id);
        }
        return $injector->getCompositionProvider($this->executable, $providers, $this->arguments);
    }
}
