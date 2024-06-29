<?php

namespace Amp\Injector;

interface AliasResolver
{
    /**
     * @param string $alias
     * @return class-string|null
     */
    public function alias(string $alias): ?string;
}
