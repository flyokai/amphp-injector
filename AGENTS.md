# flyokai/amphp-injector

> User docs → [`README.md`](README.md) · Agent quick-ref → [`CLAUDE.md`](CLAUDE.md) · Agent deep dive → [`AGENTS.md`](AGENTS.md)

Dependency injection container for PHP 8.1+ with weaver-based parameter resolution, ordered compositions, and lifecycle management.

## Core Architecture

### Application & Container

- `Application` — main entry point. Built from `Injector` + `Definitions`. Implements `Lifecycle` (start/stop). Holds the `Container` and `Injector`.
- `Container` (interface, extends PSR-11) — stores providers keyed by normalized class name. Immutable (`with()` returns clone). `RootContainer` is the default implementation.
- `Injector` — core engine that builds providers from definitions using weavers.

### Build Flow

1. Create `Definitions` collection with service/object definitions
2. Create `Injector` with a root `Weaver` (typically `any(...)` combining multiple weavers)
3. Construct `Application(injector, definitions, name, ?aliasResolver)`
4. `Application` calls `definition->build(injector)` for every definition, registering providers in Container
5. `application->start()` walks all providers, starts `Lifecycle` instances in dependency order
6. `$container->get(id)` retrieves services
7. `application->stop()` stops in reverse order

### Basic Example

```php
use Amp\Injector\{Application, Definitions, Injector};
use function Amp\Injector\{any, arguments, names, object, singleton, value};

$definitions = (new Definitions)
    ->with(singleton(object(MyService::class, arguments(names([
        'config' => value(['key' => 'val'])
    ])))), 'my_service');

$application = new Application(new Injector(any()), $definitions, 'my-app');
$application->start();

$service = $application->getContainer()->get('my_service');
```

## Definition Types

### singleton(Definition, mustStart=false)
Wraps any definition. Caches the instance — subsequent `get()` calls return same object. If `mustStart=true`, the service must be started via lifecycle before use.

```php
// Shared singleton, lazy start
singleton(object(MyService::class))

// Must be started before first get()
singleton(object(HttpServer::class), true)
```

### object(string $class, ?Arguments $arguments = null)
Prototype factory — creates new instance via constructor reflection every time. Use inside `singleton()` for shared services.

```php
object(Foobar::class)
object(Foobar::class, arguments(names([
    'a' => factory(fn() => new stdClass),
    'b' => value(42),
])))
```

### value(mixed $value)
Wraps a literal value. No construction logic.

```php
value(new PsrLogMessageProcessor)
value(['key' => 'val'])
```

### factory(Closure $factory, ?Arguments $arguments = null)
Prototype factory from a closure. New instance per `get()`. The closure can accept a `ProviderContext` to inspect the injection site:

```php
factory(function (ProviderContext $context): PsrLogger {
    $param = $context->getParameter(1);
    $name = $param?->getDeclaringClass() ?? 'unknown';
    return $logger->withName($name);
})
```

### injectableFactory(string $class, ?Closure $factory = null, ?Arguments $arguments = null)
Returns a **callable** from the container, not an instance. The callable has some parameters pre-injected by the container; remaining parameters are passed at call time.

```php
// Container resolves Baz and Qux via weavers, returns a callable:
// fn(...$runtimeArgs) => new BarImpl($resolvedBaz, $resolvedQux, ...$runtimeArgs)
injectableFactory(BarImpl::class)
```

### compositionFactory(Closure $factory, ?Definitions $definitions = null, ?Arguments $arguments = null)
Creates a composition — a collection of items built from sub-definitions. The factory receives all items as named arguments.

```php
compositionFactory(CompositionOrdered::selfFactory(), $itemDefinitions)
compositionFactory(MyCompositionImpl::selfFactory())
```

### compositionItem(Definition $definition, array $before = [], array $after = [], array $depends = [])
Wraps a definition as a `CompositionItem` with ordering constraints for use inside `CompositionOrdered`.

```php
$definitions = definitions()
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
// Result order: foo → bar → baz
```

## Weaver System (Parameter Resolution)

Weavers resolve constructor/function parameters to definitions. They're chained in `Arguments` — first match wins.

### names(array $definitions = [])
Resolves by **parameter name**. Most common weaver.

```php
arguments(names()
    ->with('config', value(['key' => 'val']))
    ->with('logger', singleton(object(Logger::class)))
)
```

### types()
Resolves by **parameter type**. Explicit mapping of class → definition. Also indexes parent classes/interfaces.

```php
types()->with(ProviderContext::class, new ProviderDefinition(new ContextProvider))
```

### runtimeTypes(Definitions, AliasResolver)
Resolves via **PHP 8 attributes** on parameters. Inspected at reflection time.

Supported attributes:
- `#[ServiceParameter]` — singleton instance of the parameter's type
- `#[SharedParameter]` — shared instance scoped to current definition
- `#[PrivateParameter]` — new instance per injection site
- `#[FactoryParameter(Class::class)]` — injectable factory returning a callable

```php
class FooImpl {
    public function __construct(
        #[PrivateParameter] protected Bar $bar,
        #[SharedParameter] protected Baz $baz,
        #[ServiceParameter] protected Qux $qux,
        #[FactoryParameter(Bar::class)] protected \Closure $barFactory,
    ) {}
}
```

### automaticTypes(Definitions, AliasResolver)
Auto-wires by type from all registered definitions. Returns a definition only if exactly one exists for that type (no ambiguity).

```php
$definitions = definitions()
    ->with(object(Foo::class))
    ->with(object(Bar::class));

// Bar's constructor parameter of type Foo is auto-resolved
$injector = new Injector(automaticTypes($definitions));
```

### any(Weaver ...$weavers)
Tries multiple weavers in sequence, returns first match. Typical setup:

```php
$injector = new Injector(any(
    automaticTypes($definitions, $aliasResolver),
    runtimeTypes(new Definitions(), $aliasResolver),
));
```

## Alias Resolution

Maps interfaces to implementations via `AliasResolverImpl`. One-way: requesting `InterfaceA` yields `ImplB`.

```php
$aliasResolver = (new AliasResolverImpl())
    ->with(Foo::class, FooImpl::class)
    ->with(Bar::class, BarImpl::class);

$injector = (new Injector(any(...)))
    ->withAlias($aliasResolver->alias(...));

$application = new Application($injector, $definitions, 'app', $aliasResolver);
// $container->get(Foo::class) → returns FooImpl instance
```

## Composition System

Ordered collections with dependency-based sorting via topological sort (`marcj/topsort`).

### CompositionOrdered
Items wrapped in `CompositionItem` with `before`, `after`, `depends` constraints:

```php
$items = definitions()
    ->with(object(CompositionItem::class, arguments()->with(names()
        ->with('after', value(['bar']))
        ->with('value', object(BazImpl::class))
    )), 'baz')
    ->with(object(CompositionItem::class, arguments()->with(names()
        ->with('value', object(BarImpl::class))
    )), 'bar');

$def = compositionFactory(CompositionOrdered::selfFactory(), $items);
```

### CompositionImpl
Simple unordered composition. Use `selfFactory()` as the factory closure.

## Lifecycle

Services implementing `Lifecycle` (start/stop) are managed by the application:
- `start()` — called after all definitions are built, walks dependency graph, starts in dependency order
- `stop()` — called on shutdown, stops in reverse order
- `singleton(definition, mustStart: true)` — service must be started before first `get()`
- `SingletonProvider->lazy()` — defer initialization to first `get()` instead of `start()`

## Proxy Support (Lazy Loading)

Lazy-loading proxies can be implemented via custom `Definition` using ProxyManager:

```php
// See examples/proxy.php for full implementation
$definitions = (new Definitions)
    ->with(proxy(Car::class, object(Car::class)), 'car')
    ->with(proxy(V8::class, object(V8::class, arguments(...))), 'engine');

// Car constructor NOT called until first method call
$car = $container->get('car');
$car->turnRight(); // NOW Car is constructed
```

## Gotchas

- **Aliases are one-way**: `Interface => Impl` lets you request by Interface. Requesting Impl directly uses Impl's own definition, not the alias.
- **Class name normalization**: All container keys are lowercased internally. Don't rely on case-sensitive lookups.
- **Ambiguous auto-wiring**: If 2+ definitions share a type, `AutomaticTypeWeaver` returns null. Use explicit `names()` or `types()` weavers.
- **Circular deps**: Not detected — causes infinite recursion. Refactor or use lazy singletons.
- **Immutable containers**: Every `with()` returns a clone. The Application holds the final reference.
- **mustStart singletons**: If `mustStart=true`, calling `get()` before `application->start()` throws `LifecycleException`.
- **Variadic parameters**: Only supported via `InjectableFactoryDefinition`. Regular `FactoryDefinition` doesn't handle variadic.
- **Proxy not built-in**: `ProxyDefinition` exists but throws "not supported yet". Use custom Definition (see examples/proxy.php).
