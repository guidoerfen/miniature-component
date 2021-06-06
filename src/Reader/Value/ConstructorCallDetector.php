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

    public function __construct(string $code, array $params, Logger $logger)
    {
        $this->code   = $code;
        $this->params = $params;
        $this->logger = $logger;
    }

    public function detect() : int
    {
        return $this->errors;
    }

}