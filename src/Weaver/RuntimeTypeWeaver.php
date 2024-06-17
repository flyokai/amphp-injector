<?php

namespace Amp\Injector\Weaver;

use Amp\Injector\Internal\Reflector;
use Amp\Injector\Meta\Parameter;
use Amp\Injector\Weaver;
use ReflectionAttribute;
use ReflectionClass;
use function Amp\Injector\Internal\getDefaultReflector;
use Amp\Injector\Definition;
use function Amp\Injector\Internal\normalizeClass;
use Amp\Injector\Meta\ParameterAttribute;
use Amp\Injector\Meta\ParameterAttribute\SharedParameter as SingletonParameter;
use Amp\Injector\Meta\ParameterAttribute\PrivateParameter as ObjectParameter;
use function Amp\Injector\object;
use function Amp\Injector\singleton;

class RuntimeTypeWeaver implements Weaver
{
    private Reflector $reflector;

    /** @var Definition[] */
    private array $definitions = [];

    public function __construct()
    {
        $this->reflector = getDefaultReflector();

    }

    public function getDefinition(Parameter $parameter): ?Definition
    {
        if (($type = $parameter->getType()) && $parameter->getDeclaringClass()) {
            $class = $parameter->getDeclaringClass();
            $nClass = normalizeClass($class);
            $key = $nClass.'::'.$parameter->getName();
            if (array_key_exists($key, $this->definitions)) {
                return $this->definitions[$key];
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
            foreach ($type->getTypes() as $type) {
                $typeReflection = null;
                try {
                    $typeReflection = new ReflectionClass($type);
                } catch (\ReflectionException) {}
                if ($typeReflection && $typeReflection->isInstantiable()) {
                    $this->definitions[$key] = $this->definitionByAttribute($injectorAttribute, $type);
                    return $this->definitions[$key];
                }
            }
        }

        return null;
    }

    protected function definitionByAttribute($parameterAttribute, $targetClass): ?Definition
    {
        if ($parameterAttribute instanceof ParameterAttribute\Factory) {
            return $parameterAttribute->createDefinition($targetClass);
        }
        return null;
    }
}