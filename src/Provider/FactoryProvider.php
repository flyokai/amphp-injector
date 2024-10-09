<?php

namespace Amp\Injector\Provider;

use Amp\Injector\InjectionException;
use Amp\Injector\Meta\Argument;
use Amp\Injector\Meta\Executable;
use Amp\Injector\Provider;
use Amp\Injector\ProviderContext;

final class FactoryProvider implements Provider
{
    private Executable $executable;
    /**
     * @var Argument[]
     */
    private array $arguments;

    public function __construct(Executable $executable, Argument ...$arguments)
    {
        $this->executable = $executable;
        $this->arguments = $arguments;
    }

    public function get(ProviderContext $context): mixed
    {
        try {
            $args = [];

            foreach ($this->arguments as $argument) {
                $parameter = $argument->getParameter();
                if ($parameter->isVariadic()) {
                    $args[$argument->getName()] = $argument->getProvider()->get($context->withParameter($parameter));
                } else {
                    $args[$argument->getPosition()] = $argument->getProvider()->get($context->withParameter($parameter));
                }
            }

            return ($this->executable)(...$args);
        } catch (\Throwable $e) {
            throw new InjectionException(
                \sprintf('Could not execute %s: %s', $this->executable, $e->getMessage()),
                $e
            );
        }
    }

    public function unwrap(): ?Provider
    {
        return null;
    }

    /**
     * @return Provider[]
     */
    public function getDependencies(): array
    {
        return array_map(fn($arg) => $arg->getProvider(), $this->arguments);
    }
}
