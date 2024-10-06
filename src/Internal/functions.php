<?php

namespace Amp\Injector\Internal;

/**
 * @param string $class
 * @param bool $throw
 * @return class-string|false
 * @throws \Error
 * @internal
 */
function normalizeClass(string $class, bool $throw = true): string|false
{
    static $cache = [];

    if (isset($cache[$class])) {
        return $cache[$class];
    }

    // See https://www.php.net/manual/en/language.oop5.basic.php
    if (!\preg_match('(^\\\\?(?:[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*\\\\)*[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$)', $class)) {
        if ($throw) {
            throw new \Error('Invalid class name: ' . $class);
        } else {
            return false;
        }
    }

    /** @var class-string $normalizedClass */
    $normalizedClass = \strtolower(\ltrim($class, '\\'));

    $cache[$class] = $normalizedClass;
    // TODO: Limit cache size?

    return $normalizedClass;
}

/** @internal */
function getDefaultReflector(): Reflector
{
    static $reflector = null;

    if (!$reflector) {
        $reflector = new CachingReflector(new StandardReflector);
    }

    return $reflector;
}
