<?php

namespace Miniature\Component\Reader;

use Miniature\Component\Reader\YamlParserDecoratorInterface;
use Miniature\Component\Reader\Value\ConfigParameters;

/**
 * Class Config
 * @package Miniature\Component\Reader
 * @package Miniature\Component
 * @author Guido Erfen <sourcecode@erfen.de>
 * @see https://github.com/guidoerfen/miniature/tree/main/Component#reading-the-configuration-directory
 */
class Config
{
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   INIT
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    // a directory to be recursively scanned
    private string  $configDir;

    // sub directories will be read or not
    private ?string $env;
    private array   $possibleEnv;

    // values, flags
    private bool    $isLoaded    = false;
    private array   $values      = [];

    // handling YAML support
    private bool    $yamlSupported;
    private ?YamlParserDecoratorInterface $yamlParserDecorator;



    public function __construct(ConfigParameters $configParameters)
    {
        // Directory to be read recursively
        $this->configDir           = $configParameters->getDirectory();

        // Environment
        $this->env                 = $configParameters->getEnv();
        $this->possibleEnv         = $configParameters->getPossibleEnv();

        // YAML-support
        $this->yamlSupported       = extension_loaded('yaml');
        $this->yamlParserDecorator = $configParameters->getYamlParserDecorator();
    }



    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   GET
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    public function getValues() : array
    {
        if (! $this->isLoaded) {
            $this->readConfig();
        }
        return $this->values;
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   DIRECTORY
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    private function readConfig() : void
    {
        $this->readConfigDir($this->configDir);
    }

    private function readConfigDir($path) : void
    {
        $list = scandir($path);
        $dirPathList = [];
        array_shift($list); // remove '.'
        array_shift($list); // remove '..'

        foreach ($list as $filename) {
            $filepath = $path.'/'.$filename;
            if (is_dir($filepath) && $this->isAllowedAddToSubPathList($filename)) {
                $dirPathList[] = $filepath;
                continue;
            }
            if (is_file($filepath)) {
                $this->readFile($filepath, $filename) ;
            }
        }

        $this->readSubDirs($dirPathList); // recursion
    }

    private function isAllowedAddToSubPathList($name) : bool
    {
        if (in_array ($name, $this->possibleEnv)) {
            if ($name === $this->env) {
                return true;
            }
            return false;
        }
        return true;
    }

    private function readSubDirs(array $dirPathList) : void
    {
        foreach ($dirPathList as $path) {
            $this->readConfigDir($path);
        }
    }









    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   FILE
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    private function readFile(string $filepath, string $filename) : void
    {
        $suffix = substr($filename, strrpos($filename, '.') + 1);
        switch ($suffix) {
            case 'php':
                $this->readPhp($filepath);
                break;
            case 'yml':
            case 'yaml':
                $this->readYml($filepath);
                break;
            default:
        }
    }

    private function readPhp(string $filepath) : void
    {
        $array = include($filepath);
        if (! is_array($array)) {
            throw new \RuntimeException("File '$filepath' is expected to return array!");
        }
        $this->addArrayContentToValueCollection($array);
    }

    /**
     * @see https://pecl.php.net/package/yaml
     */
    private function readYml(string $filepath) : void
    {
        $array = [];
        if ($this->yamlSupported) {
            $array = yaml_parse_file($filepath);
        }
        elseif ($this->yamlParserDecorator instanceof  YamlParserDecoratorInterface) {
            $array = $this->yamlParserDecorator->parseFileToYaml($filepath);
        }
        else {
            user_error(
                'No YAML support installed yet. ' .
                'Either you install the PECL package from https://pecl.php.net/package/yaml ' .
                'or you create a decorator for your favourite php-based YAML-parser implementing the ' .
                'Miniature\Component\Reader\YamlParserDecoratorInterface. ',
                E_USER_WARNING
            );
        }
        $this->addArrayContentToValueCollection($array);
    }




    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   ARRAY
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    /**
     * Crawls up to one recursive level in order to override with values read in sub-directories.
     */
    private function addArrayContentToValueCollection(array $input) : void
    {
        foreach ($input as $offset => $content) {
            if (is_iterable($content)) {
                foreach ($content as $key => $value) {
                    $this->values[$offset][$key] = $value;
                }
            }
            else {
                $this->values[$offset] = $content;
            }
        }
    }

}