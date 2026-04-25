# FORK notes — `flyokai/amphp-injector`

> User docs → [`README.md`](README.md) · Agent quick-ref → [`CLAUDE.md`](CLAUDE.md) · Agent deep dive → [`AGENTS.md`](AGENTS.md)

This package `replace`s upstream [`amphp/injector`](https://github.com/amphp/injector). It is **not** a maintenance fork — it is a substantially-evolved descendant with a different surface area.

## Upstream

- Repository: <https://github.com/amphp/injector>
- License: MIT (preserved in `LICENSE`)
- Original authors: Daniel Lowrey, Levi Morrison, Dan Ackroyd, Niklas Keller (see `LICENSE`)

## Why we forked

Upstream provides an `Injector` class with `make()` / `define()` / `share()` / `delegate()` / `prepare()` — useful for application bootstrapping but limited:

- No first-class **lifecycle** for ordered start/stop of stateful services.
- No first-class **compositions** — collections of items contributed by independent modules with topological ordering.
- No **PHP 8 attribute** integration on parameters.
- No clean separation of `Application` / `Container` / `Definitions` / `Injector` — everything hangs off `Injector`.

The Flyokai framework needs all four. We needed a deeper redesign than upstream was willing to accept, so we forked.

## What changed

| Concept | Upstream | This fork |
|---------|----------|-----------|
| Entry point | `new Injector()` and call methods | `new Application(injector, definitions, name, ?aliasResolver)` |
| State storage | Mutable `Injector` properties | Immutable `Container`, `Definitions`, `Arguments` (every `with()` clones) |
| Defining services | `$injector->define(Class, [...])` | `singleton(object(Class, arguments(names()->with(...))))` |
| Sharing | `$injector->share(Class)` | `singleton(...)` |
| Aliases | `$injector->alias(Iface, Impl)` | `AliasResolverImpl` (separate object), wired into `Injector::withAlias()` |
| Parameter resolution | Definition arrays + heuristics | **Weavers** (`names`, `types`, `runtimeTypes`, `automaticTypes`) chained via `any()` |
| Attribute-driven wiring | none | `#[ServiceParameter]`, `#[SharedParameter]`, `#[PrivateParameter]`, `#[FactoryParameter]` (resolved by `runtimeTypes()` weaver) |
| Compositions | none | `Composition`, `CompositionOrdered`, `CompositionItem` (topological sort via `before` / `after` / `depends`) |
| Lifecycle | none | `Lifecycle` interface (`start()` / `stop()`) walked in dependency order at `application->start()` / `stop()` |
| Variadic injection | not supported | supported via `injectableFactory()` |

## Compatibility

This fork **`replace`s** `amphp/injector` in `composer.json`. Other libraries that declare `require: amphp/injector` will resolve to this fork.

The classes and interfaces in `Amp\Injector\…` namespace are **not** binary-compatible with upstream. Methods such as `Injector::make()`, `Injector::define()`, `Injector::share()`, `Injector::delegate()`, and `Injector::prepare()` no longer exist. Code written for upstream `amphp/injector` will not run against this fork without a rewrite.

## Tracking upstream

Upstream is largely dormant. We do not regularly rebase or merge from it. If a security advisory affects upstream, we evaluate it against the current code and patch directly.

To compare against upstream:

```bash
git remote add upstream https://github.com/amphp/injector.git
git fetch upstream
git log --oneline upstream/master ^HEAD     # commits we don't have
git log --oneline HEAD ^upstream/master     # commits unique to fork
```

## License

MIT — both upstream and fork. See `LICENSE`. The fork adds no additional restrictions.
