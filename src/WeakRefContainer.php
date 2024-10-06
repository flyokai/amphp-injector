<?php

namespace Amp\Injector;

class WeakRefContainer implements Container
{
    /**
     * @var \WeakReference<Container>|null
     */
    protected ?\WeakReference $subject=null;

    protected function resolveSubject(): Container
    {
        if ($this->subject === null) {
            throw new \RuntimeException('Subject container is not set.');
        }
        if (!$this->subject->get()) {
            throw new \RuntimeException('Subject container no longer exist');
        }
        return $this->subject->get();
    }

    public function setSubject(Container $container): self
    {
        $this->subject = \WeakReference::create($container);
        return $this;
    }

    public function alias(string $id): ?string
    {
        return $this->resolveSubject()->alias($id);
    }

    public function get(string $id): mixed
    {
        return $this->resolveSubject()->get($id);
    }

    public function has(string $id): bool
    {
        $subject = $this->resolveSubject();
        return $this->resolveSubject()->has($id);
    }

    public function getIterator(): \Traversable
    {
        return $this->resolveSubject()->getIterator();
    }

    public function getProvider(string $id): Provider
    {
        return $this->resolveSubject()->getProvider($id);
    }

    public function with(string $id, Provider $provider): Container
    {
        return $this->resolveSubject()->with($id, $provider);
    }

    public function withAlias(\Closure $alias): Container
    {
        return $this->resolveSubject()->withAlias($alias);
    }


}
