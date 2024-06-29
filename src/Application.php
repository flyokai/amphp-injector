<?php

namespace Amp\Injector;

use Amp\Injector\Internal\ApplicationLifecycle;

final class Application implements Lifecycle
{
    private ApplicationLifecycle $lifecycle;

    /**
     * @throws InjectionException
     */
    public function __construct(
        private Injector $injector,
        Definitions $definitions,
        AliasResolver $aliasResolver = new AliasResolverImpl,
        private Container $container = new RootContainer,
    )
    {
        foreach ($definitions as $id => $definition) {
            $this->container = $this->container->with($id, $definition->build($injector));
        }
        $this->container = $this->container->withAlias($aliasResolver->alias(...));

        $this->lifecycle = new ApplicationLifecycle($this->container);
    }

    public function getInjector(): Injector
    {
        return $this->injector;
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    /**
     * @throws InjectionException
     */
    public function invoke(Definition $definition)
    {
        return $definition->build($this->injector)->get(new ProviderContext);
    }

    /**
     * @throws \Throwable
     * @throws LifecycleException
     */
    public function start(): void
    {
        $this->lifecycle->start();
    }

    /**
     * @throws LifecycleException
     */
    public function stop(): void
    {
        $this->lifecycle->stop();
    }
}
