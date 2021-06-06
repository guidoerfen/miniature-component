<?php

namespace Miniature\Component\Reader\Value;

use Miniature\DiContainer\DiContainer;
use Miniature\Component\Component;
use Miniature\Component\Reader\Logger\IlleagalConstructorCallLogger;

class IlleagalConstructorCallParameters
{
    private Component   $component;
    private DiContainer $diContainer;
    private IlleagalConstructorCallLogger $logger;
    private string      $rootPath;
    private array       $excludeDirectories = ['vendor' => true];


    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   PATH
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    private function getRealPath(string $path)   : string
    {
        return str_replace('\\', '/', realpath($path));
    }

    public function setRootPath(string $path) : self
    {
        $this->rootPath = $this->getRealPath($path). '/';
        if (! is_dir($this->rootPath) || $this->rootPath === '/') {
            throw new \InvalidArgumentException("\n".
                "String '$path' resulting in '$this->rootPath' does not point to a directory path! \n"
            );
        }
        return $this;
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   SET
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    public function setComponent(Component $component): self
    {
        $this->component = $component;
        return $this;
    }

    public function setDiContainer(DiContainer $diContainer): self
    {
        $this->diContainer = $diContainer;
        return $this;
    }

    public function setLogger(IlleagalConstructorCallLogger $logger): self
    {
        $this->logger = $logger;
        return $this;
    }

    public function setExcludeDirectories($excludeDirectories): self
    {
        foreach ($excludeDirectories as $offset => $directoryName) {
            if (is_string($directoryName)) {
                $this->excludeDirectories[trim(strtolower($directoryName))] = true;
                continue;
            }
            throw new \Exception("\n".
                "Illegal datatype in index '$offset': 'String' expected, '" .
                gettype($directoryName)."' given. \n"
            );
        }
        return $this;
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   GET
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    public function getDiContainer(): DiContainer
    {
        return $this->diContainer;
    }


    public function getExcludeDirectories() : array
    {
        return $this->excludeDirectories;
    }

    public function getRootPath() : string
    {
        return $this->rootPath;
    }

    public function getComponent(): Component
    {
        return $this->component;
    }

    public function getLogger(): IlleagalConstructorCallLogger
    {
        return $this->logger;
    }







}