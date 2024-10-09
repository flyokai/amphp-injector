<?php

namespace Amp\Injector\Internal;

use Amp\Injector\Arguments;
use Amp\Injector\InjectionException;
use Amp\Injector\Injector;
use Amp\Injector\Meta\Argument;
use Amp\Injector\Meta\Executable;
use Amp\Injector\Meta\Parameter;
use Amp\Injector\Provider;
use Amp\Injector\Provider\CompositionProvider;
use Amp\Injector\Provider\InjectableFactoryProvider;
use Amp\Injector\Provider\FactoryProvider;
use Amp\Injector\Providers;
use function Amp\Injector\value;

/** @internal */
final class ExecutableWeaver
{
    /**
     * @param Executable $executable
     * @param Arguments $arguments
     * @param Injector $injector
     * @return Provider
     * @throws InjectionException
     */
    public static function build(Executable $executable, Arguments $arguments, Injector $injector): Provider
    {
        return new FactoryProvider(
            $executable,
            ...self::buildArguments($executable, $executable->getParameters(), $arguments, $injector)
        );
    }

    /**
     * @param Executable $executable
     * @param Parameter[] $parameters
     * @param Arguments $arguments
     * @param Injector $injector
     * @return Provider
     * @throws InjectionException
     */
    public static function buildCallback(
        Executable $executable, array $parameters, Arguments $arguments, Injector $injector
    ): Provider
    {
        return new InjectableFactoryProvider(
            $executable,
            ...self::buildArguments($executable, $parameters, $arguments, $injector, true)
        );
    }

    /**
     * @param Executable $executable
     * @param Providers $providers
     * @param Arguments $arguments
     * @param Injector $injector
     * @return Provider
     */
    public static function buildComposition(
        Executable $executable, Providers $providers, Arguments $arguments, Injector $injector
    ): Provider
    {
        return new CompositionProvider(
            $executable,
            $providers
        );
    }

    /**
     * @param Executable $executable
     * @param Parameter[] $parameters
     * @param Arguments $arguments
     * @param Injector $injector
     * @param bool $silent
     * @return Argument[]
     * @throws InjectionException
     */
    private static function buildArguments(
        Executable $executable, array $parameters, Arguments $arguments, Injector $injector, bool $silent=false
    ): array
    {
        $count = \count($parameters);
        $variadic = null;
        $usedNames = $args = [];

        $lastIndex = 0;
        for ($index = 0; $index < $count; $index++) {
            $parameter = $parameters[$index];

            if ($parameter->isVariadic()) {
                $variadic = $parameter;
                continue;
            }

            $definition = $arguments->getDefinition($parameter);
            $definition ??= $parameter->isOptional() ? value($parameter->getDefaultValue()) : null;

            if ($definition === null) {
                $type = $parameter->getType();
                if ($type && $type->isNullable()) {
                    $definition = value(null);
                }
            }

            if (!$silent) {
                $definition ??= throw new InjectionException(
                    'Could not find a suitable definition for ' . $parameter->getName()
                );
            } elseif (!$definition) {
                continue;
            }

            $usedNames[] = $parameter->getName();

            $args[$index] = new Argument($parameter, $definition->build($injector), $index);
            $lastIndex = $index;
        }

        if ($variadic) {
            $names = $arguments->getNames();
            foreach ($names as $name => $definition) {
                if (!in_array($name, $usedNames)) {
                    $name = is_int($name) ? $name+$index : $name;
                    $args[$name] = new Argument($variadic, $definition->build($injector), ++$lastIndex, $name);
                }
            }
        }

        return $args;
    }
}
