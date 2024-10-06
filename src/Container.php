<?php

namespace Amp\Injector;

use Psr\Container\ContainerInterface;

/**
 * A container is responsible for (possibly indirectly) holding references to all its scoped entries.
 *
 * Entries might be application or request scoped. It does not old references to unscoped entries, i.e. entries
 * that are always recreated, so called prototypes.
 * @extends \IteratorAggregate<string, Provider>
 */
interface Container extends ContainerInterface, \IteratorAggregate
{
    public function alias(string $id): ?string;

    public function get(string $id): mixed;

    public function has(string $id): bool;

    /** @return \Traversable<string, Provider> */
    public function getIterator(): \Traversable;

    public function getProvider(string $id): Provider;

    public function with(string $id, Provider $provider): self;

    /**
     * @param \Closure(string): (string|null) $alias
     * @return self
     */
    public function withAlias(\Closure $alias): self;
}
