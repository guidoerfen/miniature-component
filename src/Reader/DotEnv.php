<?php

namespace Miniature\Component\Reader;

/**
 * Adopted from a solution by F.R Michel
 * @author F.R Michel
 * @see https://github.com/devcoder-xyz/php-dotenv
 * @package Miniature\Component\Reader
 */
class DotEnv
{
    private $filePath;
    private $isLoaded = false;
    private $values = [];

    public function __construct(string $filePath)
    {
        if (!file_exists($filePath)) {
            throw new \InvalidArgumentException("Path '$filePath' is not valid!");
        }
        if (!is_readable($filePath)) {
            throw new \RuntimeException("File '$filePath' is not readable!");
        }
        $this->filePath = $filePath;
    }

    public function getValues() : array
    {
        if (! $this->isLoaded) {
            $this->load();
        }
        return $this->values;
    }

    public function load() : void
    {
        $lines = file($this->filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos(trim($line), '#') === 0) {
                continue;
            }
            list($name, $value) = explode('=', $line, 2);
            $this->values[trim($name)] = $this->processValue($value);
        }
        $this->isLoaded = true;
    }

    private function processValue(string $value)
    {
        $trimmedValue = trim($value);
        $loweredValue = strtolower($trimmedValue);
        $isBoolean    = in_array($loweredValue, ['true', 'false'], true);

        if ($isBoolean) {
            return $loweredValue === 'true';
        }
        if ($loweredValue === 'null') {
            return null;
        }
        return $trimmedValue;
    }
}