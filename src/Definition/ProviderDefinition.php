<?php

namespace Amp\Injector\Definition;

use Amp\Injector\Arguments;
use Amp\Injector\Definition;
use Amp\Injector\InjectionException;
use Amp\Injector\Injector;
use Amp\Injector\Meta\Type;
use Amp\Injector\Provider;

final class ProviderDefinition implements Definition
{
    private Provider $provider;

    public function __construct(Provider $provider)
    {
        $this->provider = $provider;
    }

    public function getType(): ?Type
    {
        return null;
    }

    public function getAttribute(string $attribute): ?object
    {
        return null;
    }

    public function build(Injector $injector): Provider
    {
        return $this->provider;
    }

    public function hasArguments(): bool
    {
        return false;
    }

    public function prependArguments(Arguments $arguments): self
    {
        throw new InjectionException(sprintf('%s does not have arguments', __CLASS__));
    }

    public function appendArguments(Arguments $arguments): self
    {
        throw new InjectionException(sprintf('%s does not have arguments', __CLASS__));
    }
}
