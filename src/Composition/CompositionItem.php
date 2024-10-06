<?php

namespace Amp\Injector\Composition;

class CompositionItem
{
    /**
     * @var mixed[]
     */
    protected array $data;

    /**
     * @param mixed $value
     * @param string[] $before
     * @param string[] $after
     * @param string[] $depends
     * @param mixed ...$args
     */
    public function __construct(
        public readonly mixed $value,
        public readonly array $before = [],
        public readonly array $after = [],
        public array $depends = [],
        mixed ...$args
    ) {
        $this->data = $args;
    }

    /**
     * @param string $name
     * @return mixed|null
     */
    public function __get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    /**
     * @return string[]
     */
    public function depends(): array
    {
        return $this->depends;
    }

    /**
     * @param string[] $depends
     * @return self
     */
    public function withDepends(array $depends): self
    {
        $clone = clone $this;
        $clone->depends = array_filter(array_unique(array_merge($clone->depends, $depends)));
        return $clone;
    }
}
