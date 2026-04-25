# flyokai/amphp-injector

> User docs → [`README.md`](README.md) · Agent quick-ref → [`CLAUDE.md`](CLAUDE.md) · Agent deep dive → [`AGENTS.md`](AGENTS.md)

DI container for PHP 8.1+ with weaver-based parameter resolution, ordered compositions, and lifecycle management.

See [AGENTS.md](AGENTS.md) for detailed documentation.

## Quick Reference

- **Entry point**: `Application` (holds Container + Injector, implements Lifecycle)
- **Definition helpers**: `singleton()`, `object()`, `value()`, `factory()`, `injectableFactory()`, `compositionFactory()`, `compositionItem()`
- **Weavers**: `names()` (by param name), `types()` (by class), `runtimeTypes()` (PHP 8 attributes), `automaticTypes()` (auto-wire), `any()` (chain)
- **Attributes**: `#[ServiceParameter]`, `#[SharedParameter]`, `#[PrivateParameter]`, `#[FactoryParameter(Class)]`
- **Compositions**: `CompositionOrdered` with topological sort via `before`/`after`/`depends`
- **Aliases**: `AliasResolverImpl` — one-way interface→implementation mapping
- **Lifecycle**: `start()` in dependency order, `stop()` in reverse
- **Key rule**: Aliases are one-way; ambiguous auto-wiring returns null
