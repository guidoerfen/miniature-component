<?php

namespace Miniature\Component\Tests\TestEnv\Components;

use Miniature\Component\Component;

class VerboseComponent extends Component {
    protected static ?Component $instance = null;

    public function getRootPath(): ?string
    {
        return $this->getProperties()[''];
    }

    public function getEnv(): ?string
    {
        return $this->getProperties()['env'];
    }

    public function getEnvValues(): array
    {
        return $this->getProperties()['envValues'];
    }

    public function getConfigValues(): array
    {
        return $this->getProperties()['configValues'];
    }

    public function getPossibleEnv() : ?array
    {
        return $this->getProperties()['possibleEnv'];
    }

    public function getPublicAccessAllowingEnv() : ?array
    {
        return $this->getProperties()['publicAccessAllowingEnv'];
    }



}
