<?php

require __DIR__ . '/bootstrap.php';

use Amp\Injector\Application;
use Amp\Injector\Composition\Composition;
use Amp\Injector\Composition\CompositionImpl;
use Amp\Injector\Definitions;
use Amp\Injector\Injector;
use Amp\Injector\Meta\ParameterAttribute\FactoryParameter;
use Amp\Injector\Meta\ParameterAttribute\PrivateProxy;
use Amp\Injector\Meta\ParameterAttribute\ServiceParameter;
use Amp\Injector\Meta\ParameterAttribute\ServiceProxy;
use Amp\Injector\Meta\ParameterAttribute\SharedParameter;
use Amp\Injector\Meta\ParameterAttribute\PrivateParameter;
use Amp\Injector\Meta\ParameterAttribute\SharedProxy;
use function Amp\Injector\any;
use function Amp\Injector\arguments;
use function Amp\Injector\automaticTypes;
use function Amp\Injector\compositionFactory;
use function Amp\Injector\compositeObject;
use function Amp\Injector\definitions;
use function Amp\Injector\factory;
use function Amp\Injector\names;
use function Amp\Injector\object;
use function Amp\Injector\runtimeTypes;
use function Amp\Injector\singleton;
use function Amp\Injector\types;
use function Amp\Internal\formatStacktrace;

interface Foo
{

}
class FooImpl implements Foo
{
    public function __construct(
        #[PrivateParameter] protected Bar                  $bar,
        #[SharedParameter] protected Baz                   $baz,
        #[ServiceParameter] protected Qux                  $qux,
        /*#[PrivateProxy(Bar::class)] protected Bar          $barProxy,
        #[SharedProxy(Baz::class)] protected Baz           $bazProxy,
        #[ServiceProxy(Qux::class)] protected Qux          $quxProxy,*/
        /** @var callable: Bar */
        #[FactoryParameter(Bar::class)] protected \Closure $barFactory
    )
    {
    }
    public function createBar(...$args): Bar
    {
        return ($this->barFactory)(...$args);
    }
}

interface Bar
{

}
class BarImpl implements Bar
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

interface Baz
{

}
class BazImpl implements Baz
{
    public function __construct()
    {
    }
}

interface Qux
{

}
class QuxImpl implements Qux
{
    public function __construct()
    {
    }
}

interface MyComposition extends Composition
{

}

class MyCompositionImpl extends CompositionImpl implements MyComposition
{

}

interface CompositionContainer
{

}

class CompositionContainerImpl implements CompositionContainer
{
    public function __construct(
        private MyComposition $compositeArray
    ) {
    }
}

$aliasResolver = (new \Amp\Injector\AliasResolverImpl())
    ->with(Foo::class, FooImpl::class)
    ->with(Bar::class, BarImpl::class)
    ->with(Baz::class, BazImpl::class)
    ->with(Qux::class, QuxImpl::class)
    ->with(MyComposition::class, MyCompositionImpl::class)
    ->with(CompositionContainer::class, CompositionContainerImpl::class)
;

$compositionDefinitions = definitions()
    ->with(singleton(object(BazImpl::class)), 'baz1')
    ->with(singleton(object(BazImpl::class)), 'baz2')
;
$compositionWeaver = names()
    ->with('compositeArray', compositionFactory(MyCompositionImpl::selfFactory(), $compositionDefinitions))
;
$compositionArguments = arguments()
    ->with($compositionWeaver)
;

$definitions = definitions()
    ->with(object(FooImpl::class), FooImpl::class)
    ->with(object(CompositionContainerImpl::class, $compositionArguments), CompositionContainerImpl::class)
;

$runtimeTypes = runtimeTypes(
    new Definitions(), $aliasResolver
);

$automaticTypes = automaticTypes(
    $definitions, $aliasResolver
);

$injector = (new Injector(any(
    $automaticTypes,
    $runtimeTypes
)))->withAlias($aliasResolver->alias(...));

$application = new Application($injector, $definitions, $aliasResolver);

/** @var Foo $foo1 */
#$foo1 = $application->getContainer()->get(Foo::class);
/** @var Foo $foo2 */
#$foo2 = $application->getContainer()->get(Foo::class);

#var_dump($foo1);
#var_dump($foo2);

$compositionContainer1 = $application->getContainer()->get(CompositionContainer::class);
$compositionContainer2 = $application->getContainer()->get(CompositionContainer::class);

var_dump($compositionContainer1);
var_dump($compositionContainer2);

/*var_dump($foo2->createBar(qux: new Qux));
var_dump($foo2->createBar(baz: new Baz));
var_dump($foo2->createBar(new Baz, new Qux));*/
