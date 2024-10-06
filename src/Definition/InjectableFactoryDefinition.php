<?php

namespace Amp\Injector\Definition;

use Amp\Injector\Arguments;
use Amp\Injector\Definition;
use Amp\Injector\Injector;
use Amp\Injector\Meta\Executable;
use Amp\Injector\Meta\Parameter;
use Amp\Injector\Meta\Type;
use Amp\Injector\Provider;

final class InjectableFactoryDefinition implements Definition
{
    /**
     * @var Parameter[]
     */
    private array $parameters;
    private Executable $executable;
    private Arguments $arguments;

    /**
     * @param Executable $executable
     * @param Parameter[] $parameters
     * @param Arguments $arguments
     */
    public function __construct(Executable $executable, array $parameters, Arguments $arguments)
    {
        $this->executable = $executable;
        $this->arguments = $arguments;
        $this->parameters = $parameters;
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
        return $injector->getCallbackProvider($this->executable, $this->parameters, $this->arguments);
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
