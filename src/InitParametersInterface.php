<?php
declare(strict_types=1);

namespace Miniature\Component;

use Miniature\DiContainer\Syntax\MapperAbstract as DiSyntaxMapperAbstract;

/**
 * Interface InitParametersInterface
 * @package Miniature\Component
 * @author Guido Erfen <sourcecode@erfen.de>
 */
interface InitParametersInterface
{
    public function getAppRootPath()                    : ?string;

    public function getConfigDirectoryPath()            : ?string;

    public function getDotEnvFilePath()                 : ?string;

    public function getEnv()                            : ?string;

    public function getDiSyntaxMapper()                 : ?DiSyntaxMapperAbstract;

    public function getPossibleEnv()                    : ?array;

    public function setAppRootPath(string $path)        : self;

    public function initAsAverageDomainRoot()           : self;

    public function setConfigDirectory(string $dirName) : self;

    public function setDotEnvPath(string $dirName = '') : self;

    public function setEnv(string $env)                 : self;

    public function setDiSyntaxMapper(DiSyntaxMapperAbstract $mapper) : self;
}