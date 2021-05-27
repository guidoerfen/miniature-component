<?php
//require 'C:/__Git/_guidoerfen/Miniature/Component/src/Reader/Value/ConfigParameters.php';
function miniature_component_test_autoload($class)
{
    $fileName = str_replace('\\', '/', realpath(__DIR__) . '/' . $class ) . '.php';
    echo "$fileName<br>\n";
    if (is_file($fileName)) {
        require $fileName;
    }
    else {
        if (preg_match('/^(.*\/Miniature)\/(\w+)\/tests\/((\w+\/)*)(\w+)\.php/', $fileName)) {
            $newFileName = preg_replace(
                '/^(.*\/Miniature)\/(\w+)\/tests\/((\w+\/)*)(\w+)\.php/',
                '$1/$2/tests/$3$5.php',
                $fileName
            );
            echo "NEW $newFileName<br>\n";
            if (is_file($newFileName)) {
                require $newFileName;
            }
        }
        elseif (preg_match('/^(.*)\/(App.*)\/(\w+\/)*(\w+)\.php/', $fileName)) {
            $newFileName = preg_replace(
                '/^(.*)\/(App.*)\/(\w+\/)*(\w+)\.php/',
                '$1/src/$2/$3$4.php',
                $fileName
            );
            if (is_file($newFileName)) {
                require $newFileName;
            }
            #echo "$newFileName<br>\n";
        }
        #echo "$fileName<br>\n";
    }
}
spl_autoload_register('miniature_component_test_autoload');
