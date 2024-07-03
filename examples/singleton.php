<?php

use Amp\Injector\Application;
use Amp\Injector\Definitions;
use Amp\Injector\Injector;
use Amp\Injector\ProviderContext;
use function Amp\Injector\any;
use function Amp\Injector\arguments;
use function Amp\Injector\names;
use function Amp\Injector\object;
use function Amp\Injector\singleton;
use function Amp\Injector\value;

require __DIR__ . '/bootstrap.php';

class Singleton
{
    public stdClass $std;

    public function __construct(stdClass $std)
    {
        $this->std = $std;
    }
}

$stdClass = new stdClass;
$stdClass->foo = "foobar";

$definitions = (new Definitions)
    ->with(singleton(object(Singleton::class, arguments(names(['std' => value($stdClass)])))), 'hello_world')
    ->with(singleton(object(Singleton::class, arguments(names(['std' => value($stdClass)]))), true), 'on_start')
;

$application = new Application(new Injector(any()), $definitions, 'hello');

$application->start();

//$a = $application->getContainer()->get('hello_world');
$a = $application->getContainer()->get('on_start');

print $a->std->foo . PHP_EOL;

$a->std->foo = 'baz';

// Note: Returns the same object
$a = $application->getContainer()->get('hello_world');

print $a->std->foo . PHP_EOL;
