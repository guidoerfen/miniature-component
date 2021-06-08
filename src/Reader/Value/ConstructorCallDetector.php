<?php

namespace Miniature\Component\Reader\Value;

use Miniature\DiContainer\DiContainer;
use Miniature\Component\Reader\Logger\IlleagalConstructorCallLogger as Logger;

class ConstructorCallDetector
{
    private string $code;
    private array $params;
    private Logger $logger;
    private int $errors = 0;

    private string $filePath;
    private string $declarationFile;
    private string $classNameSimple;
    private string $classNameFullyQualified;
    private string $classNameFullyQualifiedRegex;
    private string $classUseStatement;
    private string $staticMethod;
    private string $constructorCallRegex;
    private string $constructorCallRegexFull;

    public function __construct(string $code, array $params, Logger $logger)
    {
        $this->code   = $code;
        $this->params = $params;
        $this->logger = $logger;
        foreach ($params as $property => $stringValue) {
            if (is_string($stringValue)) {
                $this->$property = $stringValue;
            }
        }
    }

    public function detect() : int
    {
        return $this->errors;
    }

}