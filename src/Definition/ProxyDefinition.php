<?php

namespace Amp\Injector\Definition;

use Amp\Injector\Definition;
use Amp\Injector\Injector;
use Amp\Injector\Meta\Type;
use Amp\Injector\ProviderContext;
use Amp\Injector\Provider;
use ProxyManager\Factory\LazyLoadingValueHolderFactory;
use function Amp\Injector\factory;

class ProxyDefinition implements Definition
{
    public function __construct(
        private string $class,
        private Definition $definition
    ) {
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
        $factory = new LazyLoadingValueHolderFactory;

        return factory(fn () => $factory->createProxy(
            $this->class,
            function (&$object, $proxy, $method, $parameters, &$initializer) use ($injector) {
                $object = $this->definition->build($injector)->get(new ProviderContext);
                $initializer = null;
            }
        ))->build($injector);
    }
}