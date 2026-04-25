# flyokai/amphp-injector

> User docs → [`README.md`](README.md) · Agent quick-ref → [`CLAUDE.md`](CLAUDE.md) · Agent deep dive → [`AGENTS.md`](AGENTS.md)

> A dependency injection container for PHP 8.1+ with weaver-based parameter resolution, ordered compositions, attribute-driven wiring, and lifecycle management.

`flyokai/amphp-injector` is a substantially-evolved descendant of [`amphp/injector`](https://github.com/amphp/injector). The container is now driven by **weavers** (composable parameter resolvers), built around an immutable **`Application`** that owns a **`Container`** and an **`Injector`**, with first-class support for **`Composition`** collections, **PHP 8 attributes**, **lifecycle** hooks, and **alias resolution**.

> **Heads up.** Despite the package name and namespace, this is a fork. If you arrived here looking for upstream `amphp/injector`'s `Injector::make()`, `define()`, `share()`, `delegate()` API — that API is gone. Read on for what replaced it.

## Features

- **`Application`** — entry point that holds a `Container` and `Injector`, implements `Lifecycle`
- **Definition helpers** — `singleton()`, `object()`, `value()`, `factory()`, `injectableFactory()`, `compositionFactory()`, `compositionItem()`
- **Weavers** — `names()`, `types()`, `runtimeTypes()`, `automaticTypes()`, `any()` for parameter resolution
- **PHP 8 attributes** — `#[ServiceParameter]`, `#[SharedParameter]`, `#[PrivateParameter]`, `#[FactoryParameter(Class)]`
- **Compositions** — `CompositionOrdered` with topological sort via `before` / `after` / `depends`
- **Aliases** — one-way interface → implementation via `AliasResolverImpl`
- **Lifecycle** — `start()` in dependency order, `stop()` in reverse
- **Lazy proxies** — pluggable via `ProxyDefinition` (see `examples/proxy.php`)

## Installation

```bash
composer require flyokai/amphp-injector
```

The package's `composer.json` `replace`s `amphp/injector` so the namespace `Amp\Injector\…` resolves to this fork.

## Quick start

```php
use Amp\Injector\Application;
use Amp\Injector\Definitions;
use Amp\Injector\Injector;
use function Amp\Injector\{any, arguments, names, object, singleton, value};

class MyService
{
    public function __construct(public array $config) {}
}

$definitions = (new Definitions())
    ->with(
        singleton(object(MyService::class, arguments(names()
            ->with('config', value(['key' => 'val']))
        ))),
        'my_service',
    );

$application = new Application(new Injector(any()), $definitions, 'my-app');
$application->start();

/** @var MyService $svc */
$svc = $application->getContainer()->get('my_service');

$application->stop();
```

## Build flow

1. Create a `Definitions` collection containing service / object / value / factory / composition definitions.
2. Create an `Injector` with a root `Weaver` (typically `any(...)` chaining several weavers).
3. Construct `Application(injector, definitions, name, ?aliasResolver)`.
4. The `Application` calls `definition->build($injector)` for every definition and registers providers in the `Container`.
5. `application->start()` walks the providers and starts every `Lifecycle` instance in dependency order.
6. `$container->get($id)` retrieves services.
7. `application->stop()` stops in reverse order.

## Definition helpers

### `singleton(Definition, mustStart = false)`

Wraps any definition to cache the instance — subsequent `get()` calls return the same object. `mustStart=true` requires the service to be started before first `get()`.

```php
singleton(object(MyService::class));
singleton(object(HttpServer::class), mustStart: true);
```

### `object(string $class, ?Arguments $arguments = null)`

Prototype factory — creates a new instance via constructor reflection every call. Wrap in `singleton()` for sharing.

```php
object(Foobar::class)
object(Foobar::class, arguments(names()
    ->with('a', factory(fn() => new \stdClass()))
    ->with('b', value(42))
))
```

### `value(mixed $value)`

Wraps a literal — no construction logic.

```php
value(['key' => 'val'])
value(new \Monolog\Processor\PsrLogMessageProcessor())
```

### `factory(\Closure $factory, ?Arguments $arguments = null)`

Prototype factory from a closure. The closure can accept a `ProviderContext` to inspect the injection site:

```php
factory(function (ProviderContext $context): PsrLogger {
    $param = $context->getParameter(1);
    $name  = $param?->getDeclaringClass() ?? 'unknown';
    return $logger->withName($name);
})
```

### `injectableFactory(string $class, ?\Closure $factory = null, ?Arguments $arguments = null)`

Returns a **callable** from the container, not an instance. Some parameters are pre-injected; remaining ones are passed at call time:

```php
injectableFactory(BarImpl::class)
// fn(...$runtimeArgs): BarImpl => new BarImpl($injectedBaz, $injectedQux, ...$runtimeArgs)
```

### `compositionFactory(\Closure $factory, ?Definitions $itemDefinitions = null, ?Arguments $arguments = null)`

Creates a composition — a collection of items built from sub-definitions. The factory receives all items as named arguments.

```php
compositionFactory(CompositionOrdered::selfFactory(), $itemDefinitions)
compositionFactory(MyCompositionImpl::selfFactory())
```

### `compositionItem(Definition $definition, array $before = [], array $after = [], array $depends = [])`

Wraps a definition as a `CompositionItem` for use inside `CompositionOrdered`:

```php
$itemDefs = definitions()
    ->with(object(CompositionItem::class, arguments()->with(names()
        ->with('after', value(['bar']))
        ->with('value', object(BazImpl::class))
    )), 'baz')
    ->with(object(CompositionItem::class, arguments()->with(names()
        ->with('before', value(['bar']))
        ->with('value', object(FooImpl::class))
    )), 'foo')
    ->with(object(CompositionItem::class, arguments()->with(names()
        ->with('value', object(BarImpl::class))
    )), 'bar');

// Final order: foo → bar → baz
```

## Weavers

Weavers resolve constructor / function parameters to definitions. They're chained inside `Arguments` — first match wins.

### `names(array $definitions = [])`

Resolves by **parameter name** — the most common weaver:

```php
arguments(names()
    ->with('config', value(['key' => 'val']))
    ->with('logger', singleton(object(Logger::class)))
)
```

### `types(array $definitions = [])`

Resolves by **parameter type** — explicit class → definition mapping. Also indexes parent classes / interfaces.

```php
types()->with(ProviderContext::class, new ProviderDefinition(new ContextProvider()))
```

### `runtimeTypes(Definitions $defs, AliasResolver $aliasResolver)`

Resolves via **PHP 8 attributes** on parameters. Supported attributes:

| Attribute | Resolves to |
|-----------|-------------|
| `#[ServiceParameter]` | shared singleton instance of the parameter's type |
| `#[SharedParameter]` | shared instance scoped to the current definition |
| `#[PrivateParameter]` | new instance per injection site |
| `#[FactoryParameter(Class::class)]` | injectable factory (callable) returning a `Class` instance |

```php
class FooImpl
{
    public function __construct(
        #[PrivateParameter] protected Bar         $bar,
        #[SharedParameter]  protected Baz         $baz,
        #[ServiceParameter] protected Qux         $qux,
        #[FactoryParameter(Bar::class)] protected \Closure $barFactory,
    ) {}

    public function makeBar(): Bar { return ($this->barFactory)(); }
}
```

### `automaticTypes(Definitions $defs, AliasResolver $aliasResolver)`

Auto-wires by type from all registered definitions. Returns a definition only if **exactly one** matches the type — ambiguous matches return `null`.

```php
$defs = definitions()
    ->with(object(Foo::class))
    ->with(object(Bar::class));

$injector = new Injector(automaticTypes($defs));
// Bar's constructor parameter of type Foo is auto-resolved.
```

### `any(Weaver ...$weavers)`

Tries multiple weavers in order, returns the first match. Typical setup:

```php
$injector = new Injector(any(
    automaticTypes($defs, $aliasResolver),
    runtimeTypes(new Definitions(), $aliasResolver),
));
```

## Alias resolution

Maps interfaces to implementations via `AliasResolverImpl`. Aliases are **one-way** — requesting `Foo` yields `FooImpl`, but requesting `FooImpl` directly resolves through `FooImpl`'s own definition.

```php
$aliasResolver = (new \Amp\Injector\AliasResolverImpl())
    ->with(Foo::class, FooImpl::class)
    ->with(Bar::class, BarImpl::class);

$injector = (new Injector(any(...)))
    ->withAlias($aliasResolver->alias(...));

$application = new Application($injector, $definitions, 'app', $aliasResolver);
$application->getContainer()->get(Foo::class);   // → FooImpl
```

## Compositions (ordered collections)

`CompositionOrdered` items get topologically sorted via `before` / `after` / `depends`:

```php
$items = definitions()
    ->with(object(CompositionItem::class, arguments()->with(names()
        ->with('after', value(['bar']))
        ->with('value', object(BazImpl::class))
    )), 'baz')
    ->with(object(CompositionItem::class, arguments()->with(names()
        ->with('value', object(BarImpl::class))
    )), 'bar');

$ordered = compositionFactory(CompositionOrdered::selfFactory(), $items);
```

`CompositionImpl` is the simple unordered variant. Use `selfFactory()` as the factory closure for both.

## Lifecycle

Services implementing `Lifecycle` are managed by the application:

- `start()` — called after every definition is built; walks the dependency graph; starts in dependency order
- `stop()` — called on shutdown; reverse order
- `singleton($definition, mustStart: true)` — service must be started before first `get()`
- `SingletonProvider->lazy()` — defer initialization to first `get()` instead of `start()`

## Lazy proxies

Pluggable via custom `Definition`s using `ocramius/proxy-manager`. See `examples/proxy.php`:

```php
$definitions = (new Definitions())
    ->with(proxy(Car::class, object(Car::class)),  'car')
    ->with(proxy(V8::class,  object(V8::class)),   'engine');

$car = $container->get('car');   // Car constructor NOT called yet
$car->turnRight();                // NOW Car is constructed
```

The built-in `ProxyDefinition` currently throws `not supported yet` — provide your own `Definition` subclass.

## Examples

The `examples/` directory contains runnable scripts for every feature:

| File | Demonstrates |
|------|--------------|
| `singleton.php` | Basic singleton + value definitions |
| `runtime.php` | Attribute-driven runtime types + compositions |
| `delegation.php` | Factories and `injectableFactory` |
| `logger.php` | `ProviderContext` for site-aware factories |
| `proxy.php` | Lazy-loading proxy definitions |
| `benchmark.php` | Performance harness |

## Gotchas

- **Aliases are one-way.** `Interface ⇒ Impl` lets you request the interface. Requesting `Impl` directly uses `Impl`'s own definition, not the alias.
- **Class names are normalised to lowercase** internally — don't rely on case-sensitive keys.
- **Ambiguous auto-wiring** — if two definitions share a type, `automaticTypes` returns `null`. Disambiguate with `names()` or `types()`.
- **Circular dependencies are not detected** — they cause infinite recursion. Refactor or use `lazy` singletons.
- **Containers are immutable** — every `with()` returns a clone; the `Application` holds the final reference.
- **`mustStart` singletons** — `get()` before `application->start()` throws `LifecycleException`.
- **Variadic parameters** — only supported via `injectableFactory()`. Plain `factory()` doesn't pass variadics through.
- **Built-in `ProxyDefinition` is not implemented** — see `examples/proxy.php` for a custom approach.

## Differences from upstream `amphp/injector`

| Upstream | This fork |
|----------|-----------|
| `Injector::make()`, `define()`, `share()`, `delegate()`, `prepare()` | gone — replaced by `Application` + `Definitions` + weavers |
| `define()` arrays | `arguments(names()->with(...))` |
| `share()` | `singleton(...)` |
| `alias()` (Injector method) | `AliasResolverImpl` (separate object) |
| Per-injector instance API | Immutable `Application` / `Container` / `Definitions` |
| No compositions | `Composition`, `CompositionOrdered`, `CompositionItem` first-class |
| No attribute-driven wiring | `#[ServiceParameter]`, `#[SharedParameter]`, `#[PrivateParameter]`, `#[FactoryParameter]` |
| No formal lifecycle | `Lifecycle::start()` / `stop()` walked in dependency order |

## See also

- [`flyokai/application`](../application/README.md) — uses this DI as the runtime container; see also `vendor/flyokai/flyokai/docs/dependency-injection.md` for diconfig structure
- [`flyokai/composition`](../composition/README.md) — module-level topological sort (different layer than `CompositionOrdered`)
- [`flyokai/generic`](../generic/README.md) — `TunerContainer` / `ExecutionContainer` use compositions internally
- Original: <https://github.com/amphp/injector>

## License

MIT
