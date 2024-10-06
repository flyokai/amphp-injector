<?php

namespace Amp\Injector\Definition;

use Amp\Injector\Arguments;
use Amp\Injector\Definition;
use Amp\Injector\Injector;
use Amp\Injector\Meta\Type;
use Amp\Injector\Provider;
use Amp\Injector\ServiceDefinition;

final class SingletonDefinition implements Definition, ServiceDefinition
{
    /**
     * @var \WeakMap<Injector, Provider>
     */
    private \WeakMap $instances;
    private Definition $definition;

    public function __construct(
        Definition $definition,
        public readonly bool $mustStart = false
    ) {
        $this->definition = $definition;
        $this->instances = new \WeakMap;
    }

    public function getType(): ?Type
    {
        return $this->definition->getType();
    }

    public function getAttribute(string $attribute): ?object
    {
        return $this->definition->getAttribute($attribute);
    }

    public function build(Injector $injector): Provider
    {
        return $this->instances[$injector] ??= new Provider\SingletonProvider(
            $this->definition->build($injector),
            $this->mustStart
        );
    }

    public function hasArguments(): bool
    {
        return $this->definition->hasArguments();
    }

    public function prependArguments(Arguments $arguments): self
    {
        $this->definition->prependArguments($arguments);
        return $this;
    }

    public function appendArguments(Arguments $arguments): self
    {
        $this->definition->appendArguments($arguments);
        return $this;
    }
}
