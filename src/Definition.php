<?php

namespace Amp\Injector;

use Amp\Injector\Meta\Type;

interface Definition
{
    public function getType(): ?Type;

    public function getAttribute(string $attribute): ?object;

    public function hasArguments(): bool;

    public function prependArguments(Arguments $arguments): self;

    public function appendArguments(Arguments $arguments): self;

    /**
     * @throws InjectionException
     */
    public function build(Injector $injector): Provider;
}
