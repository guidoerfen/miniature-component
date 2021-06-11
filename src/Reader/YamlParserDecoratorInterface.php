<?php
declare(strict_types=1);

namespace Miniature\Component\Reader;

interface YamlParserDecoratorInterface
{
    public function parseFileToYaml(string $filePath) : array;
}