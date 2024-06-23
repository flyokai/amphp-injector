<?php

require __DIR__ . '/bootstrap.php';

use Amp\Injector\Application;
use Amp\Injector\Injector;
use Amp\Injector\Meta\ParameterAttribute\FactoryParameter;
use Amp\Injector\Meta\ParameterAttribute\ServiceParameter;
use Amp\Injector\Meta\ParameterAttribute\SharedParameter;
use Amp\Injector\Meta\ParameterAttribute\PrivateParameter;
use function Amp\Injector\any;
use function Amp\Injector\automaticTypes;
use function Amp\Injector\definitions;
use function Amp\Injector\factory;
use function Amp\Injector\object;
use function Amp\Injector\runtimeTypes;
use function Amp\Injector\singleton;
use function Amp\Injector\types;
use function Amp\Internal\formatStacktrace;

class Foo
{
    public function __construct(
        #[PrivateParameter] protected Bar $bar,
        #[SharedParameter] protected Baz $baz,
        #[ServiceParameter] protected Qux $qux,
        /** @var callable: Bar */
        #[FactoryParameter(Bar::class)] protected \Closure $barFactory
    ) {
    }
    public function createBar(...$args): Bar
    {
        return ($this->barFactory)(...$args);
    }
}

class Bar
{
    public function __construct(
        #[SharedParameter] protected Baz $baz,
        #[ServiceParameter] protected Qux $qux
    ) {
    }
    public function test()
    {
        return 'test from bar';
    }
}

class Baz
{
    public function __construct()
    {
    }
}

class Qux
{
    public function __construct()
    {
    }
}

$definitions = definitions()
    ->with(object(Foo::class), Foo::class)
;

$runtimeTypes = runtimeTypes();

$application = new Application(new Injector(any(
    automaticTypes($definitions),
    $runtimeTypes
)), $definitions);

/** @var Foo $foo1 */
$foo1 = $application->getContainer()->get(Foo::class);
/** @var Foo $foo2 */
$foo2 = $application->getContainer()->get(Foo::class);

var_dump($foo1);
var_dump($foo2);

var_dump($foo2->createBar(qux: new Qux));
var_dump($foo2->createBar(baz: new Baz));
var_dump($foo2->createBar(new Baz, new Qux));
