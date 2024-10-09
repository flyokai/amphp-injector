<?php

namespace Amp\Injector\Meta;

use Amp\Injector\Provider;

final class Argument
{
    private Parameter $parameter;
    private Provider $provider;
    private int $position;
    private int|string $name;

    public function __construct(Parameter $parameter, Provider $provider, int $position, int|string $name = null)
    {
        $this->parameter = $parameter;
        $this->provider = $provider;
        $this->position = $position;
        $this->name = $name??$parameter->getName();
    }

    public function getParameter(): Parameter
    {
        return $this->parameter;
    }

    public function getProvider(): Provider
    {
        return $this->provider;
    }

    public function getName(): int|string
    {
        return $this->name;
    }

    public function getPosition(): int
    {
        return $this->position;
    }
}
