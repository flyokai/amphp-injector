<?php

namespace Amp\Injector\Provider;

use Amp\Injector\Meta\Argument;
use Amp\Injector\Meta\Executable;
use Amp\Injector\Provider;
use Amp\Injector\ProviderContext;

final class InjectableFactoryProvider implements Provider
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
        $__args = [];
        $__named = [];
        foreach ($this->arguments as $index=>$argument) {
            $__name = $argument->getParameter()->getName();
            $__arg = $argument->getProvider()->get($context->withParameter($argument->getParameter()));
            $__args[$index] = $__arg;
            $__named[$__name] = $__arg;
        }
        $__exec = $this->executable;
        return static function (...$args) use ($__named, $__args, $__exec) {
            if (is_numeric(key($args))) {
                $args = array_replace_recursive($__args, $args);
            } else {
                $args = array_replace_recursive($__named, $args);
            }
            return ($__exec)(...$args);
        };
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
