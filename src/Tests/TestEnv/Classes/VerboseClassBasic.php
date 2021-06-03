<?php

namespace Miniature\Component\Tests\TestEnv\Classes;

class VerboseClassBasic
{
    protected array $args;

    public function __constrct()
    {
        $this->args = func_get_args();
    }

    public function getArgs(): array
    {
        return $this->args;
    }
}