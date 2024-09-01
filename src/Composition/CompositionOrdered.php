<?php

namespace Amp\Injector\Composition;

use MJS\TopSort\Implementations\StringSort;

class CompositionOrdered extends CompositionImpl
{
    /**
     * @param object<string, CompositionItem>|array<string, CompositionItem> $array
     * @param int $flags
     * @param string $iteratorClass
     */
    public function __construct(object|array $array = [], int $flags = 0, string $iteratorClass = "ArrayIterator")
    {
        foreach ($array as $name => $item) {
            if (!is_string($name)) {
                throw new \InvalidArgumentException(sprintf(
                    '%s $array argument keys must be string', self::class
                ));
            }
            if (!$item instanceof CompositionItem) {
                throw new \InvalidArgumentException(sprintf(
                    '%s array argument values must be instance of %s',
                    self::class, CompositionItem::class
                ));
            }
            foreach ($item->before as $before) {
                if (isset($array[$before])) {
                    $array[$before] = $array[$before]->withDepends([$name]);
                }
            }
            $array[$name] = $array[$name]->withDepends($item->after);
        }
        $sort = new StringSort;
        foreach ($array as $name => $item) {
            $sort->add($name, $item->depends());
        }
        $sortedNames = $sort->sort();
        $sorted = [];
        foreach ($sortedNames as $name) {
            $sorted[] = $array[$name]->value;
        }
        parent::__construct($sorted, $flags, $iteratorClass);
    }
}
