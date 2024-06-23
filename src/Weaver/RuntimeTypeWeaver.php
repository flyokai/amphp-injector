<?php

namespace Amp\Injector\Weaver;

use Amp\Injector\Definitions;
use Amp\Injector\Internal\Reflector;
use Amp\Injector\Meta\Parameter;
use Amp\Injector\Weaver;
use ReflectionAttribute;
use ReflectionClass;
use function Amp\Injector\Internal\getDefaultReflector;
use Amp\Injector\Definition;
use function Amp\Injector\Internal\normalizeClass;
use Amp\Injector\Meta\ParameterAttribute;

class RuntimeTypeWeaver implements Weaver
{
    private Reflector $reflector;

    public function __construct(
        public Definitions $runtimeDefinitions = new Definitions()
    ) {
        $this->reflector = getDefaultReflector();
    }

    public function getDefinition(Parameter $parameter): ?Definition
    {
        if (($type = $parameter->getType()) && $parameter->getDeclaringClass()) {
            $class = $parameter->getDeclaringClass();
            $nClass = normalizeClass($class);
            $key = $nClass.'::'.$parameter->getName();
            if ($this->runtimeDefinitions->get($key)) {
                return $this->runtimeDefinitions->get($key);
            }
            $injectorAttribute = null;
            if ($parameter->hasAttribute(
                ParameterAttribute::class,
                ReflectionAttribute::IS_INSTANCEOF
            )) {
                $injectorAttribute = $parameter->getAttribute(
                    ParameterAttribute::class,
                    ReflectionAttribute::IS_INSTANCEOF
                );
            }
            if (!$injectorAttribute) {
                return null;
            }
            if ($injectorAttribute instanceof ParameterAttribute\FactoryParameter) {
                $this->processFactoryParameter($injectorAttribute, $key);
                return $this->runtimeDefinitions->get($key);
            }
            foreach ($type->getTypes() as $type) {
                $typeReflection = null;
                try {
                    $typeReflection = new ReflectionClass($type);
                } catch (\ReflectionException) {}
                if ($typeReflection && $typeReflection->isInstantiable()) {
                    $this->processParameterAttribute($injectorAttribute, $type, $key);
                    if ($this->runtimeDefinitions->get($key)) {
                        return $this->runtimeDefinitions->get($key);
                    }
                }
            }
        }

        return null;
    }

    protected function factoryClass($class)
    {
        return $class.'Factory';
    }

    protected function processFactoryParameter($parameterAttribute, $key): void
    {
        if (!$this->runtimeDefinitions->get($key)) {
            if ($parameterAttribute instanceof ParameterAttribute\FactoryParameter) {
                $targetClass = $this->factoryClass($parameterAttribute->class);
                $definition = $this->runtimeDefinitions->get($targetClass);
                if (!$definition) {
                    $definition = $parameterAttribute->createDefinition($targetClass);
                    $this->addDefinition($definition, $targetClass);
                }
                $this->addDefinition($definition, $key);
            }
        }
    }

    protected function processParameterAttribute($parameterAttribute, $targetClass, $key): void
    {
        if (!$this->runtimeDefinitions->get($key)) {
            if ($parameterAttribute instanceof ParameterAttribute\Service) {
                $definition = $this->runtimeDefinitions->get($targetClass);
                if (!$definition) {
                    $definition = $parameterAttribute->createDefinition($targetClass);
                    $this->addDefinition($definition, $targetClass);
                }
                $this->addDefinition($definition, $key);
            } elseif ($parameterAttribute instanceof ParameterAttribute\Factory) {
                $definition = $parameterAttribute->createDefinition($targetClass);
                $this->addDefinition($definition, $key);
            }
        }
    }

    protected function addDefinition(Definition $definition, string $id): RuntimeTypeWeaver
    {
        $this->runtimeDefinitions = $this->runtimeDefinitions->with($definition, $id);
        return $this;
    }
}