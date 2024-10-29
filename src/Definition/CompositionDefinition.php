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

    public function getDefinitions(): Definitions
    {
        return $this->definitions;
    }

    public function extendDefinitions(Definitions $with): self
    {
        foreach ($with as $key => $definition) {
            $this->definitions = $this->definitions->with($definition, $key);
        }
        return $this;
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
        $processed = [];
        $providers = new Providers();
        foreach ($this->definitions as $id=>$definition) {
            $__id = strtolower($id);
            if (array_key_exists($__id, $processed)) continue;
            $providers = $providers->with($definition->build($injector), $id);
            $processed[$__id] = true;
        }
        return $injector->getCompositionProvider($this->executable, $providers, $this->arguments);
    }

    public function hasArguments(): bool
    {
        return true;
    }

    public function prependArguments(Arguments $arguments): self
    {
        $this->arguments = $arguments->merge($this->arguments);
        return $this;
    }

    public function appendArguments(Arguments $arguments): self
    {
        $this->arguments = $this->arguments->merge($arguments);
        return $this;
    }
}
