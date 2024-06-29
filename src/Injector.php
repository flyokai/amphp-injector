<?php

namespace Amp\Injector;

use Amp\Injector\Internal\ExecutableWeaver;
use Amp\Injector\Meta\Executable;

final class Injector
{
    /** @var callable(string): string */
    private \Closure $alias;

    public function __construct(
        private Weaver $weaver
    ) {
        $this->alias = fn($a) => null;
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

    public function alias($alias): ?string
    {
        return ($this->alias)($alias);
    }

    /**
     * @throws InjectionException
     */
    public function getExecutableProvider(Executable $executable, Arguments $arguments): Provider
    {
        // TODO: Make customizable?
        return ExecutableWeaver::build($executable, $arguments->with($this->weaver), $this);
    }

    public function getCallbackProvider(Executable $executable, array $parameters, Arguments $arguments): Provider
    {
        // TODO: Make customizable?
        return ExecutableWeaver::buildCallback($executable, $parameters, $arguments->with($this->weaver), $this);
    }

    public function getCompositionProvider(Executable $executable, Providers $providers, Arguments $arguments): Provider
    {
        return ExecutableWeaver::buildComposition($executable, $providers, $arguments->with($this->weaver), $this);
    }

}
