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
        $clone = clone $this;
        $id ??= $clone->generateId($definition);
        if (false !== ($__id = normalizeClass($id, false))) {
            $id = $__id;
        }
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
        if (false !== ($__id = normalizeClass($id, false))) {
            $id = $__id;
        }
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
