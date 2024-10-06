<?php

namespace Amp\Injector\Meta;

interface Parameter
{
    public function getName(): string;

    public function isOptional(): bool;

    public function isVariadic(): bool;

    public function getType(): ?Type;

    public function hasAttribute(string $attribute, int $flags = 0): bool;

    /**
     * @param class-string<ParameterAttribute> $attribute
     * @param $flags
     * @return ParameterAttribute|null
     */
    public function getAttribute(string $attribute, int $flags = 0): ?ParameterAttribute;

    public function getDefaultValue(): mixed;

    public function getDeclaringClass(): ?string;

    public function getDeclaringFunction(): ?string;
}
