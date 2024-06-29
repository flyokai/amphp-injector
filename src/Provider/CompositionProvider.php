<?php

namespace Amp\Injector\Provider;

use Amp\Injector\Definitions;
use Amp\Injector\InjectionException;
use Amp\Injector\Meta\Argument;
use Amp\Injector\Meta\Executable;
use Amp\Injector\Provider;
use Amp\Injector\ProviderContext;
use Amp\Injector\Providers;

final class CompositionProvider implements Provider
{
    private Executable $executable;
    private Providers $providers;

    public function __construct(Executable $executable, Providers $providers)
    {
        $this->executable = $executable;
        $this->providers = $providers;
    }

    public function get(ProviderContext $context): mixed
    {
        $args = [];
        foreach ($this->providers as $id=>$provider) {
            $args[$id] = $provider->get($context);
        }
        return ($this->executable)(...$args);
    }

    public function unwrap(): ?Provider
    {
        return null;
    }

    public function getDependencies(): array|Providers
    {
        return $this->providers;
    }
}
