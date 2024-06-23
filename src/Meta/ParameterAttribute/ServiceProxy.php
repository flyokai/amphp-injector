<?php

namespace Amp\Injector\Meta\ParameterAttribute;

use Attribute;
use Amp\Injector\Arguments;

#[Attribute(Attribute::TARGET_PARAMETER)]
class ServiceProxy implements Service, ProxyParameter
{
    public function __construct(
        readonly public string $class
    ) {
    }
}