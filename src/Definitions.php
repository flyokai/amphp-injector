<?php

namespace Amp\Injector;

use function Amp\Injector\Internal\normalizeClass;

/**
 * @implements \IteratorAggregate<string, Definition>
 */
final class Definitions implements \IteratorAggregate
{
    private static int $nextId = 0;

    /** @var Definition[] */
    private array $definitions = [];

    public function with(Definition $definition, ?string $id = null): self
    {
        $clone = clone $this;
        $id ??= $clone->generateId($definition);
        $clone->definitions[$id] = $definition;
        if (false !== ($__id = normalizeClass($id, false))) {
            $clone->definitions[$__id] = $definition;
        }

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
     * @return \Generator<string, Definition>
     */
    public function getIterator(): \Generator
    {
        foreach ($this->definitions as $id => $definition) {
            yield $id => $definition;
        }
    }
}
