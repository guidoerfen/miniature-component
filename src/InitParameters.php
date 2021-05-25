<?php

namespace Miniature\Component;

use Miniature\DiContainer\Syntax\MapperAbstract as DiSyntaxMapperAbstract;
use Miniature\Component\Reader\YamlParserDecoratorInterface;
use http\Exception\RuntimeException;

/**
 * Class InitParameters
 *
 * Initial-parameters value object for constructor-injection to heir of Miniature\Component\Component.
 * Feel free to create your own implemention if the InitParametersInterface if this here does not meet your needs.
 *
 * @package Miniature\Component
 * @author Guido Erfen <sourcecode@erfen.de>
 * @see https://github.com/guidoerfen/miniature/tree/main/Component#parameter-injecting-path-to-the-configuration-directory
 * @see https://github.com/guidoerfen/miniature/tree/main/Component#configuring-environments
 */
class InitParameters implements InitParametersInterface
{
    private ?string $appRootPath             = null;
    private ?string $configDirectoryPath     = null;
    private ?string $dotEnvFilePath          = null;
    private ?string $env                     = null;
    private ?array  $possibleEnv             = null;
    private ?array  $envAllowingPublicAccess = null;
    private ?DiSyntaxMapperAbstract        $diSyntaxMapper      = null;
    private ?YamlParserDecoratorInterface  $yamlParserDecorator = null;


    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   get
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    private function getRealPath(string $path)   : string
    {
        return str_replace('\\', '/', realpath($path));
    }

    public function getAppRootPath()             : ?string
    {
        return $this->appRootPath;
    }

    public function getConfigDirectoryPath()     : ?string
    {
        return $this->configDirectoryPath;
    }

    public function getDotEnvFilePath()          : ?string
    {
        return $this->dotEnvFilePath;
    }

    public function getEnv()                     : ?string
    {
        return $this->env;
    }

    public function getPossibleEnv()             : ?array
    {
        return $this->possibleEnv;
    }

    public function getEnvAllowingPublicAccess() : ?array
    {
        return $this->envAllowingPublicAccess;
    }

    public function getDiSyntaxMapper()          : ?DiSyntaxMapperAbstract
    {
        return $this->diSyntaxMapper;
    }

    public function getYamlParserDecorator()     : ?YamlParserDecoratorInterface
    {
        return $this->yamlParserDecorator;
    }



    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   root-path
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    private function rootPathIsSet() : bool
    {
        if (is_string($this->appRootPath)) {
            return true;
        }
        return false;
    }

    public function setAppRootPath(string $path) : self
    {
        $this->appRootPath = $this->getRealPath($path). '/';
        if (! is_dir($this->appRootPath)) {
            throw new \InvalidArgumentException("String '$path' does not point to a directory path!");
        }
        return $this;
    }

    /**
     * This assumes an average domain-installation with the .env-file and the /config-folder in the root directory.
     */
    public function initAsAverageDomainRoot() : self
    {
        if (! $this->rootPathIsSet()) {
            user_error(
                'Call setAppRootPath($path) first! This method will not do anything right now.',
                E_USER_WARNING
            );
            return $this;
        }
        $this->setConfigDirectoryPath('config');
        $this->setDotEnvPath();
        return $this;
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   /config
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    private function setConfigDirectoryPath(string $path) : self
    {
        $this->configDirectoryPath = $this->getRealPath($path). '/';
        if (! is_dir($this->configDirectoryPath) || $this->configDirectoryPath === '/') {
            throw new \InvalidArgumentException(
                "String '$path' resulting in '$this->configDirectoryPath' does not point to a directory path!" .
                "Use setConfigDirectory(\$dirName) if you want to add a directory name to root path '$this->appRootPath'."
            );
        }
        return $this;
    }

    public function setConfigDirectory(string $dirName) : self
    {
        if (! $this->rootPathIsSet()) {
            return $this->setConfigDirectoryPath($dirName);
        }
        $this->configDirectoryPath = $this->getRealPath($this->appRootPath . $dirName) . '/';
        if (! is_dir($this->configDirectoryPath) || $this->configDirectoryPath === '/') {
            return $this->setConfigDirectoryPath($dirName);
        }
        return $this;
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   .env
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    private function setDotEnvByRealPath(string $path) : self
    {
        $this->dotEnvFilePath = $this->getRealPath($path). '/.env';
        if (! is_file($this->dotEnvFilePath) || $this->dotEnvFilePath === '/') {
            throw new \InvalidArgumentException(
                "String '$path' resulting in '$this->dotEnvFilePath' does not point to a directory path!" .
                "Use setDotEnvFromRoot(\$dirName) if you want to add a file name to root path '$this->appRootPath'."
            );
        }
        return $this;
    }

    /**
     * empty parameter / empty string means:
     * .env is to be found in root path.
     */
    public function setDotEnvPath(string $dirName = '') : self
    {
        if (! $this->rootPathIsSet()) {
            return $this->setDotEnvByRealPath($dirName);
        }
        $this->dotEnvFilePath = $this->getRealPath($this->appRootPath . $dirName) . '/.env';
        if (! is_file($this->dotEnvFilePath) || $this->dotEnvFilePath === '/') {
            return $this->setDotEnvByRealPath($dirName);
        }
        return $this;
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   env as value (overrides .env-values)
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    public function setEnv(string $env) : self
    {
        $this->env = $env;
        return $this;
    }

    public function setAvailableEnv($possibleEnv): self
    {
        if (is_string($possibleEnv)) {
            $possibleEnv = func_get_args();
        }
        if (! is_array($possibleEnv)) {
            throw new \InvalidArgumentException(sprintf("\n" .
                    'Only array of strings or a parameter-list of strings as value allowed. ' . "\n" .
                    'Invalid value: % given.',
                    gettype($possibleEnv))
            );
        }
        foreach ($possibleEnv as $string) {
            if (! is_string($string)) {
                throw new \InvalidArgumentException(sprintf(
                    'Only strings as value allowed. Invalid value: % given.',
                    gettype($string))
                );
            }
        }
        $this->possibleEnv = $possibleEnv;
        return $this;
    }

    public function setEnvAllowingPublicAccess($tolerantEnvs): self
    {
        if (is_string($tolerantEnvs)) {
            $tolerantEnvs = func_get_args();
        }
        if (! is_array($tolerantEnvs)) {
            throw new \InvalidArgumentException(sprintf("\n" .
                    'Only array of strings or a parameter-list of strings as value allowed. ' . "\n" .
                    'Invalid value: % given.',
                    gettype($tolerantEnvs))
            );
        }
        foreach ($tolerantEnvs as $string) {
            if (! is_string($string)) {
                throw new \InvalidArgumentException(sprintf(
                    'Only strings as value allowed. Invalid value: % given.',
                    gettype($string))
                );
            }
        }
        $this->envAllowingPublicAccess = $tolerantEnvs;
        return $this;
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   objects
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    public function setDiSyntaxMapper(DiSyntaxMapperAbstract $mapper) : self
    {
        $this->diSyntaxMapper = $mapper;
        return $this;
    }

    public function setYamlParserDecorator(YamlParserDecoratorInterface $decorator) : self
    {
        $this->yamlParserDecorator = $decorator;
        return $this;
    }
}