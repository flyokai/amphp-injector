<?php

namespace Amp\Injector;

interface AliasResolver
{
    /**
     * @param string $id
     * @return class-string|null
     */
    public function alias(string $id): ?string;
}
