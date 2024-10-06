<?php

namespace Amp\Injector\Weaver;

use Amp\Injector\Definition;
use Amp\Injector\Meta\Parameter;
use Amp\Injector\Weaver;

final class AnyWeaver implements Weaver, NameWise
{
    /**
     * @var Weaver[]
     */
    private array $weavers;

    public function __construct(Weaver ...$weavers)
    {
        $this->weavers = $weavers;
    }

    public function getDefinition(int|string|Parameter $parameter): ?Definition
    {
        foreach ($this->weavers as $weaver) {
            if (is_scalar($parameter) && !$weaver instanceof NameWise) {
                continue;
            }
            if ($definition = $weaver->getDefinition($parameter)) {
                return $definition;
            }
        }

        return null;
    }
}
