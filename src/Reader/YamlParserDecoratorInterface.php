<?php

namespace Miniature\Component\Reader;

interface YamlParserDecoratorInterface
{
    public function parseFileToYaml(string $filePath) : array;
}