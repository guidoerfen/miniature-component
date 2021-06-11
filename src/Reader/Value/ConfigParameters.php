<?php
declare(strict_types=1);

namespace Miniature\Component\Reader\Value;

use Miniature\Component\Reader\YamlParserDecoratorInterface;

/**
 * Value-object as a parameter for the constructor-injection of
 * Miniature\Component\Reader\Config
 */
class ConfigParameters
{
    private string  $directory;
    private ?string $env;
    private array   $possibleEnv;
    private ?YamlParserDecoratorInterface $yamlParserDecorator = null;




    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   get
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    /**
     * @return string
     */
    public function getDirectory(): string
    {
        return $this->directory;
    }

    /**
     * @return array
     */
    public function getPossibleEnv(): array
    {
        return $this->possibleEnv;
    }

    /**
     * @return string
     */
    public function getEnv(): ?string
    {
        return $this->env;
    }

    public function getYamlParserDecorator(): ?YamlParserDecoratorInterface
    {
        return $this->yamlParserDecorator;
    }




    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   set
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */


    public function setEnv(?string $env): self
    {
        $this->env = $env;
        return $this;
    }

    public function setAvailableEnv(array $possibleEnv): self
    {
        foreach ($possibleEnv as $string) {
            if (! is_string($string)) {
                throw new \InvalidArgumentException(sprintf('Only strings allowed. Invalid value: % given.', gettype($string)));
            }
        }
        $this->possibleEnv = $possibleEnv;
        return $this;
    }

    public function setDirectory(string $directory): self
    {
        if (!is_dir($directory)) {
            throw new \InvalidArgumentException("Path '$directory' is not a directory!");
        }
        $this->directory = $directory;
        return $this;
    }

    public function setYamlParserDecorator(?YamlParserDecoratorInterface $yamlParserDecorator): self
    {
        $this->yamlParserDecorator = $yamlParserDecorator;
        return $this;
    }


}