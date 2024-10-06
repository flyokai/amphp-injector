<?php

namespace Amp\Injector;

use Amp\Injector\Internal\ExecutableWeaver;
use Amp\Injector\Meta\Executable;
use Amp\Injector\Meta\Parameter;

final class Injector
{
    /** @var \Closure(string): (string|null) */
    private \Closure $alias;

    public function __construct(
        private Weaver $weaver
    ) {
        $this->alias = fn($a) => null;
    }

    /**
     * @param \Closure(string): (string|null) $alias
     * @return $this
     */
    public function withAlias(\Closure $alias): self
    {
        $clone = clone $this;
        $clone->alias = $alias;

        return $clone;
    }

    public function alias(string $alias): ?string
    {
        return ($this->alias)($alias);
    }

    /**
     * @param Executable $executable
     * @param Arguments $arguments
     * @return Provider
     * @throws InjectionException
     */
    public function getExecutableProvider(Executable $executable, Arguments $arguments): Provider
    {
        // TODO: Make customizable?
        return ExecutableWeaver::build($executable, $arguments->with($this->weaver), $this);
    }

    /**
     * @param Executable $executable
     * @param Parameter[] $parameters
     * @param Arguments $arguments
     * @return Provider
     * @throws InjectionException
     */
    public function getCallbackProvider(Executable $executable, array $parameters, Arguments $arguments): Provider
    {
        // TODO: Make customizable?
        return ExecutableWeaver::buildCallback($executable, $parameters, $arguments->with($this->weaver), $this);
    }

    /**
     * @param Executable $executable
     * @param Providers $providers
     * @param Arguments $arguments
     * @return Provider
     */
    public function getCompositionProvider(Executable $executable, Providers $providers, Arguments $arguments): Provider
    {
        return ExecutableWeaver::buildComposition($executable, $providers, $arguments->with($this->weaver), $this);
    }

}
