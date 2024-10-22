<?php

namespace Amp\Injector\Weaver;

use Amp\Injector\AliasResolver;
use Amp\Injector\AliasResolverImpl;
use Amp\Injector\Definition;
use Amp\Injector\Definitions;
use Amp\Injector\InjectionException;
use Amp\Injector\Internal\Reflector;
use Amp\Injector\Meta\Parameter;
use Amp\Injector\Weaver;
use function Amp\Injector\Internal\getDefaultReflector;
use function Amp\Injector\Internal\normalizeClass;

// TODO: Build precompiled version with this registry as fallback
final class AutomaticTypeWeaver implements Weaver
{
    private Reflector $reflector;

    /** @var Definition[][] */
    private array $definitions = [];

    /** @var \Closure(string): (string|null) */
    private \Closure $alias;

    public function __construct(
        Definitions $definitions,
        AliasResolver $aliasResolver = new AliasResolverImpl()
    ) {
        $this->reflector = getDefaultReflector();
        $this->alias = $aliasResolver->alias(...);

        foreach ($definitions as $id => $definition) {
            if ($type = $definition->getType()) {
                foreach ($type->getTypes() as $type) {
                    $resolvedType = $this->resolveType($type);
                    $key = normalizeClass($resolvedType);
                    $this->definitions[$key][$id] = $definition;

                    foreach ($this->reflector->getParents($type) as $parent) {
                        $resolvedParent = $this->resolveType($parent);
                        $key = normalizeClass($resolvedParent);
                        $this->definitions[$key][$id] = $definition;
                    }
                }
            }
        }
    }

    private function resolveType(string $type): string
    {
        return ($this->alias)($type) ?? $type;
    }

    public function getDefinition(int|string|Parameter $parameter): ?Definition
    {
        if (is_scalar($parameter)) {
            throw new InjectionException('int|string parameter is not supported');
        }
        if ($type = $parameter->getType()) {
            foreach ($type->getTypes() as $type) {
                $key = normalizeClass($type);
                if (isset($this->definitions[$key])) {
                    $candidates = $this->definitions[$key];
                    if (\count($candidates) === 1) {
                        return \reset($candidates);
                    }
                }
            }
        }

        return null;
    }
}
