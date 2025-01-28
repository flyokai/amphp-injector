<?php

namespace Amp\Injector;

use Amp\Injector\Composition\CompositionItem;
use Amp\Injector\Definition\CompositionDefinition;
use Amp\Injector\Definition\InjectableFactoryDefinition;
use Amp\Injector\Definition\FactoryDefinition;
use Amp\Injector\Definition\ProviderDefinition;
use Amp\Injector\Definition\ProxyDefinition;
use Amp\Injector\Definition\SingletonDefinition;
use Amp\Injector\Meta\Reflection\ReflectionConstructorExecutable;
use Amp\Injector\Meta\Reflection\ReflectionFunctionExecutable;
use Amp\Injector\Provider\ValueProvider;
use Amp\Injector\Weaver\AnyWeaver;
use Amp\Injector\Weaver\AutomaticTypeWeaver;
use Amp\Injector\Weaver\NameWeaver;
use Amp\Injector\Weaver\RuntimeTypeWeaver;
use Amp\Injector\Weaver\TypeWeaver;

function definitions(): Definitions
{
    static $definitions = null;

    if (!$definitions) {
        $definitions = new Definitions;
    }

    return $definitions;
}

function arguments(Weaver ...$weavers): Arguments
{
    static $empty = null;

    if (!$empty) {
        $empty = new Arguments;
    }

    $arguments = $empty;

    foreach ($weavers as $weaver) {
        $arguments = $arguments->with($weaver);
    }

    return $arguments;
}

function singleton(Definition $definition, bool $mustStart=false): SingletonDefinition
{
    return new SingletonDefinition($definition, $mustStart);
}

/**
 * @param \Closure|null $factory
 * @param class-string $class
 * @param Arguments|null $arguments
 * @return InjectableFactoryDefinition
 * @throws InjectionException
 * @throws \ReflectionException
 */
function injectableFactory(?\Closure $factory, string $class, ?Arguments $arguments = null): InjectableFactoryDefinition
{
    $ctorExecutable = new ReflectionConstructorExecutable($class);
    if ($factory==null) {
        $executable = $ctorExecutable;
    } else {
        $executable = new ReflectionFunctionExecutable(new \ReflectionFunction($factory));
    }
    $arguments ??= arguments();

    return new InjectableFactoryDefinition($executable, $ctorExecutable->getParameters(), $arguments);
}

function compositionFactory(
    \Closure $factory,
    ?Definitions $definitions = null,
    ?Arguments $arguments = null
): CompositionDefinition {
    $executable = new ReflectionFunctionExecutable(new \ReflectionFunction($factory));
    $arguments ??= arguments();
    $definitions ??= definitions();

    return new CompositionDefinition($executable, $definitions, $arguments);
}

/**
 * @param class-string $class
 * @param Definitions|null $definitions
 * @param Arguments|null $arguments
 * @return CompositionDefinition
 * @throws InjectionException
 */
function compositeObject(
    string $class,
    ?Definitions $definitions = null,
    ?Arguments $arguments = null
): CompositionDefinition {
    $executable = new ReflectionConstructorExecutable($class);
    $arguments ??= arguments();
    $definitions ??= definitions();

    return new CompositionDefinition($executable, $definitions, $arguments);
}

/**
 * @param Definition $definition
 * @param string[] $before
 * @param string[] $after
 * @param string[] $depends
 * @param Arguments|null $arguments
 * @param mixed ...$args
 * @return FactoryDefinition
 */
function compositionItem(
    Definition $definition,
    array $before = [],
    array $after = [],
    array $depends = [],
    ?Arguments $arguments = null,
    ...$args
): FactoryDefinition {
    $arguments ??= arguments();
    $names = names()
        ->with('before', value($before))
        ->with('after', value($after))
        ->with('depends', value($depends))
        ->with('value', $definition);

    foreach ($args as $ak=>$av) {
        $names = $names->with($ak, value($av));
    }

    $arguments = $arguments->with($names);

    return object(
        CompositionItem::class,
        $arguments
    );
}

function factory(\Closure $factory, ?Arguments $arguments = null): FactoryDefinition
{
    $executable = new ReflectionFunctionExecutable(new \ReflectionFunction($factory));
    $arguments ??= arguments();

    return new FactoryDefinition($executable, $arguments);
}

/**
 * @param class-string $class
 * @param Arguments|null $arguments
 * @return FactoryDefinition
 * @throws InjectionException
 */
function object(string $class, ?Arguments $arguments = null): FactoryDefinition
{
    $executable = new ReflectionConstructorExecutable($class);
    $arguments ??= arguments();

    return new FactoryDefinition($executable, $arguments);
}

function proxy(string $class, Definition $definition): ProxyDefinition
{
    return new ProxyDefinition($class, $definition);
}

function value(mixed $value): Definition
{
    // TODO: Expose type?
    return new ProviderDefinition(new ValueProvider($value));
}

function automaticTypes(
    Definitions $definitions,
    AliasResolver $aliasResolver = new AliasResolverImpl()
): AutomaticTypeWeaver {
    return new AutomaticTypeWeaver($definitions, $aliasResolver);
}

function runtimeTypes(
    Definitions $definitions = new Definitions(),
    AliasResolver $aliasResolver = new AliasResolverImpl()
): RuntimeTypeWeaver {
    return new RuntimeTypeWeaver($definitions, $aliasResolver);
}

/**
 * @param Definition[] $definitions
 * @return NameWeaver
 */
function names(array $definitions = []): NameWeaver
{
    $names = new NameWeaver;

    foreach ($definitions as $name => $definition) {
        $names = $names->with($name, $definition);
    }

    return $names;
}

function types(): TypeWeaver
{
    return new TypeWeaver;
}

function any(Weaver ...$weavers): AnyWeaver
{
    return new AnyWeaver(...$weavers);
}
