<?php

namespace Amp\Injector;

use function Amp\Injector\Internal\normalizeClass;

final class Definitions implements \IteratorAggregate
{
    private static int $nextId = 0;

    /** @var Definition[] */
    private array $definitions = [];

    public function with(Definition $definition, ?string $id = null): self
    {
        try {
            $id = normalizeClass($id);
        } catch (\Error) {}
        $clone = clone $this;
        $clone->definitions[$id ?? $clone->generateId($definition)] = $definition;

        return $clone;
    }

    private function generateId(Definition $definition): string
    {
        $type = $definition->getType();

        return '#' . self::$nextId++ . ($type !== null ? '-' . \implode('-', $type->getTypes()) : '');
    }

    public function get(string $id): ?Definition
    {
        try {
            $id = normalizeClass($id);
        } catch (\Error) {}
        return $this->definitions[$id] ?? null;
    }

    /**
     * @return iterable<Definition>
     */
    public function getIterator(): \Generator
    {
        foreach ($this->definitions as $id => $definition) {
            yield $id => $definition;
        }
    }
}
