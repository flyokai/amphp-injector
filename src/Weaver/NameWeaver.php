<?php

namespace Amp\Injector\Weaver;

use Amp\Injector\Definition;
use Amp\Injector\Meta\Parameter;
use Amp\Injector\Weaver;

final class NameWeaver implements Weaver, NameWise
{
    private array $names = [];

    public function with(int|string $name, Definition $definition): self
    {
        $clone = clone $this;
        $clone->names[$name] = $definition;

        return $clone;
    }

    public function getDefinition(int|string|Parameter $parameter): ?Definition
    {
        $name = $parameter instanceof Parameter ? $parameter->getName() : $parameter;
        return $this->names[$name] ?? null;
    }

    public function getNames(): array
    {
        return $this->names;
    }
}
