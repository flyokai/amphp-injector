<?php

namespace Amp\Injector;

final class Providers implements \IteratorAggregate
{
    private static int $nextId = 0;

    /** @var Provider[] */
    private array $providers = [];

    public function with(Provider $provider, ?string $id = null): self
    {
        $clone = clone $this;
        $clone->providers[$id ?? $clone->generateId($provider)] = $provider;

        return $clone;
    }

    private function generateId(Provider $provider): string
    {
        return '#' . self::$nextId++;
    }

    public function get(string $id): ?Provider
    {
        return $this->providers[$id] ?? null;
    }

    /**
     * @return iterable<Provider>
     */
    public function getIterator(): \Generator
    {
        foreach ($this->providers as $id => $provider) {
            yield $id => $provider;
        }
    }
}
