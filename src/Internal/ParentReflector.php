<?php

namespace Amp\Injector\Internal;

interface ParentReflector
{
    /**
     * @param class-string $class
     * @return string[]
     * @throws \ReflectionException
     */
    public function getParents(string $class): array;
}
