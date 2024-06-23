<?php

(static function (): void {
    $paths = [
        dirname(__DIR__, 3) . "/autoload.php",
        dirname(__DIR__) . "/vendor/autoload.php",
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            $autoloadPath = $path;
            break;
        }
    }

    if (!isset($autoloadPath)) {
        fwrite(STDERR, "Could not locate autoload.php");
        exit(1);
    }

    require $autoloadPath;
})();

use function Amp\Internal\formatStacktrace;

function dumpDebugBacktrace()
{
    echo "\n".debugBacktrace()."\n\n";
}

function debugBacktrace()
{
    $trace = \debug_backtrace(\DEBUG_BACKTRACE_IGNORE_ARGS);
    \array_shift($trace);
    return formatStacktrace($trace);
}