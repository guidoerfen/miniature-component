<?php

// These tests run outside the PSR4 address room.
// This loads all Miniature classes.

function miniature_component_test_autoload($class)
{
    $fileName = str_replace('\\', '/', realpath(__DIR__) . '/' . $class ) . '.php';

    if (is_file($fileName)) {
        require $fileName;
    }
    else {
        if (preg_match('/^(.*\/Miniature)\/(\w+)\/((\w+\/)*)(\w+)\.php/', $fileName)) {
            $newFileName = preg_replace(
                '/^(.*\/Miniature)\/(\w+)\/((\w+\/)*)(\w+)\.php/',
                '$1/$2/src/$3$5.php',
                $fileName
            );
            if (is_file($newFileName)) {
                require $newFileName;
            }
        }
    }
}
spl_autoload_register('miniature_component_test_autoload');
