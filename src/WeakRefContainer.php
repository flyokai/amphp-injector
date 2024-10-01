<?php

namespace Amp\Injector;

class WeakRefContainer implements Container
{
    protected ?\WeakReference $subject=null;

    public function assertSubject()
    {
        if ($this->subject === null) {
            throw new \RuntimeException('Subject container is not set.');
        }
        if (!$this->subject->get()) {
            throw new \RuntimeException('Subject container no longer exist');
        }
    }

    public function setSubject(Container $container): self
    {
        $this->subject = \WeakReference::create($container);
        return $this;
    }

    public function alias(string $id): ?string
    {
        $this->assertSubject();
        return $this->subject->get()->alias($id);
    }

    public function get(string $id): mixed
    {
        return $this->subject->get()->get($id);
    }

    public function has(string $id): bool
    {
        return $this->subject->get()->has($id);
    }

    public function getIterator(): \Traversable
    {
        return $this->subject->get()->getIterator();
    }

    public function getProvider(string $id): Provider
    {
        return $this->subject->get()->getProvider($id);
    }

}
