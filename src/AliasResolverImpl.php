<?php

namespace Amp\Injector;

use function Amp\Injector\Internal\normalizeClass;

class AliasResolverImpl implements AliasResolver
{
    /**
     * @var array<string, class-string>
     */
    protected $alias = [];

    public function with(string $alias, string $name): self
    {
        $clone = clone $this;
        $alias = normalizeClass($alias);
        $name = normalizeClass($name);
        $clone->alias[$alias] = $name;
        return $clone;
    }

    public function alias(string $alias): ?string
    {
        $alias = normalizeClass($alias);
        return $this->alias[$alias]??null;
    }

}
