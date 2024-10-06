<?php

namespace Amp\Injector;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

interface Provider
{
    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     */
    public function get(ProviderContext $context): mixed;

    /**
     * @return Provider|null Unwrap decorated provider, or null if none.
     */
    public function unwrap(): ?Provider;

    /**
     * @return Providers|array<Provider>
     */
    public function getDependencies(): array|Providers;
}
