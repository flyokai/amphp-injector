<?php

namespace Amp\Injector;

use Amp\Injector\Meta\Parameter;
use Amp\Injector\Weaver\NameWeaver;
use Amp\Injector\Weaver\NameWise;

// TODO Readd index
final class Arguments implements Weaver, NameWise
{
    /** @var Weaver[] */
    private array $weavers = [];

    public function with(Weaver $weaver): self
    {
        $clone = clone $this;
        $clone->weavers[] = $weaver;
        return $clone;
    }

    public function merge(Arguments $arguments): self
    {
        $clone = clone $this;
        foreach ($arguments->weavers as $weaver) {
            $clone->weavers[] = $weaver;
        }
        return $clone;
    }

    public function getDefinition(int|string|Parameter $parameter): ?Definition
    {
        foreach ($this->weavers as $weaver) {
            if (is_scalar($parameter) && !$weaver instanceof NameWise) {
                continue;
            }
            if ($definition = $weaver->getDefinition($parameter)) {
                return $definition;
            }
        }

        return null;
    }

    /**
     * @return Definition[]
     */
    public function getNames(): array
    {
        $names = [];
        foreach ($this->weavers as $weaver) {
            if ($weaver instanceof NameWeaver) {
                $names = array_merge($names, $weaver->getNames());
            }
        }
        return $names;
    }
}
