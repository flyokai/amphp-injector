<?php

namespace Amp\Injector\Composition;

class CompositionItem
{
    protected array $data;
    public function __construct(
        public readonly mixed $value,
        public readonly array $before = [],
        public readonly array $after = [],
        public array $depends = [],
        ...$args
    ) {
        $this->data = $args;
    }

    public function __get(string $name)
    {
        return $this->data[$name] ?? null;
    }

    public function depends(): array
    {
        return $this->depends;
    }

    public function withDepends(array $depends): self
    {
        $clone = clone $this;
        $clone->depends = array_filter(array_unique(array_merge($clone->depends, $depends)));
        return $clone;
    }
}
