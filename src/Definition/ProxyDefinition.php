<?php

namespace Amp\Injector\Definition;

use Amp\Injector\Arguments;
use Amp\Injector\Definition;
use Amp\Injector\InjectionException;
use Amp\Injector\Injector;
use Amp\Injector\Meta\Type;
use Amp\Injector\ProviderContext;
use Amp\Injector\Provider;
use function Amp\Injector\factory;

class ProxyDefinition implements Definition
{
    public function __construct(
        private string $class,
        private Definition $definition
    ) {
        throw new InjectionException(sprintf('%s is not supported yet', __CLASS__));
    }

    public function getType(): Type
    {
        return new Type($this->class);
    }

    public function getAttribute(string $attribute): ?object
    {
        return $this->definition->getAttribute($attribute);
    }

    public function build(Injector $injector): Provider
    {
        /*$factory = new \ProxyManager\Factory\LazyLoadingValueHolderFactory;

        return factory(fn () => $factory->createProxy(
            $this->class,
            function (&$object, $proxy, $method, $parameters, &$initializer) use ($injector) {
                $object = $this->definition->build($injector)->get(new ProviderContext);
                $initializer = null;
            }
        ))->build($injector);*/
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
