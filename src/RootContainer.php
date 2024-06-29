<?php

namespace Amp\Injector;

use function Amp\Injector\Internal\normalizeClass;

final class RootContainer implements Container
{
    /** @var Provider[] */
    private array $providers = [];

    /** @var callable(string): string */
    private \Closure $alias;

    public function __construct()
    {
        $this->alias = fn($a) => null;
    }

    public function with(string $id, Provider $provider): self
    {
        $clone = clone $this;
        try {
            $id = normalizeClass($id);
        } catch (\Error) {}
        $clone->providers[$id] = $provider;

        return $clone;
    }

    /**
     * @param callable(string): string|null $alias
     * @return $this
     */
    public function withAlias(\Closure $alias): self
    {
        $clone = clone $this;
        $clone->alias = $alias;

        return $clone;
    }

    public function get(string $id): mixed
    {
        return $this->getProvider($id)->get(new ProviderContext);
    }

    /**
     * @throws NotFoundException
     */
    public function getProvider(string $id): Provider
    {
        $id = $this->id($id);
        return $this->providers[$id] ?? throw new NotFoundException('Unknown identifier: ' . $id);
    }

    public function has(string $id): bool
    {
        $id = $this->id($id);
        return isset($this->providers[$id]);
    }

    private function id(string $id): string
    {
        return normalizeClass($this->alias($id) ?? $id);
    }

    public function alias(string $id): ?string
    {
        return ($this->alias)($id);
    }

    public function getIterator(): \Generator
    {
        foreach ($this->providers as $id => $provider) {
            yield (string) $id => $provider;
        }
    }
}
