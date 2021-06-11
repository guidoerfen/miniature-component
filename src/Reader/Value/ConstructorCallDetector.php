<?php
declare(strict_types=1);

namespace Miniature\Component\Reader\Value;

use Miniature\DiContainer\DiContainer;
use Miniature\Component\Reader\Logger\IlleagalConstructorCallLogger as Logger;
use Miniature\Component\Reader\IlleagalConstructorCall;

/**
 * Class ConstructorCallDetector
 * @package Miniature\Component\Reader\Value
 * @author Guido Erfen <sourcecode@erfen.de>
 */
class ConstructorCallDetector
{
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *        INIT
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    private array $params;
    private Logger $logger;
    private IlleagalConstructorCall $reader;
    private int $errors = 0;
    private bool $headerIsWritten = false;

    private string $indent = '    ';
    private string $code;

    private string $filePath;
    private string $declarationFile;
    private string $classNameSimple;
    private string $classNameFullyQualified;
    private string $classNameFullyQualifiedRegex;
    private string $classUseStatementRegex;
    private string $staticMethod;
    private string $constructorCallRegex;
    private string $constructorCallRegexFull;

    public function __construct(string $code, array $params, Logger $logger, IlleagalConstructorCall $reader)
    {
        $this->code   = $code;
        $this->params = $params;
        $this->logger = $logger;
        $this->reader = $reader;
        foreach ($params as $property => $stringValue) {
            if (is_string($stringValue)) {
                $this->$property = $stringValue;
            }
        }
    }


    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *        WRITE
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    private function writeDetectionHeader() : void
    {
        if ($this->headerIsWritten) {
            return;
        }
        $this->logger->writeLine('');
        $this->logger->writeLine('');
        $this->logger->writeDSecondLevel("\n" .
            "Violation found in file \n'$this->filePath'\n\n" .
            "Wiring declared in file \n'$this->declarationFile'\n\n" .
            "Class concerned:        \n'$this->classNameFullyQualified'\n\n"
        );
    }

    private function writeMatchList(array $callMatches) : void
    {
        for ($i = 0; $i < count($callMatches); $i++) {
            for ($ii = 0; $ii < count($callMatches[$i]); $ii++) {
                $this->logger->write($this->indent. ($i + 1) . '.');
                if (is_string($callMatches[$i][$ii])) {
                    $this->logger->writeBlock(
                        $callMatches[$i][$ii], $this->indent
                    );
                }
                $this->logger->writeLine('');
                $this->logger->writeLine('');
                $this->errors++;
            }
        }
    }




    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *        DETECT
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    public function detect() : int
    {
        $this->detectFullQualifiedCall();
        $this->detectUseStaement();
        return $this->errors;
    }

    private function detectUseStaement() : void
    {
        preg_match($this->classUseStatementRegex, $this->code, $matches);
        if (empty($matches)) {
            return;
        }

        $aliasName            = '';
        $useStatement         = $matches[0];
        $constructorCallRegex = $this->constructorCallRegex;
        if (count($matches) == 3) {
            $aliasName = $matches[2];
            $constructorCallRegex = str_replace($this->classNameSimple, $aliasName, $constructorCallRegex);
        }

        preg_match_all($constructorCallRegex, $this->code, $callMatches, PREG_SET_ORDER);
        if (empty($callMatches)) {
            return;
        }

        $this->writeDetectionHeader();

        if (! empty($useStatement)) {
            $this->logger->writeLine('');
            $this->logger->writeLine($this->indent . 'Found \'use\'-statement: ' . $useStatement);
            $this->logger->writeLine('');
        }

        $this->writeMatchList($callMatches);
    }



    private function detectFullQualifiedCall() : void
    {
        preg_match_all($this->constructorCallRegexFull, $this->code, $callMatches, PREG_SET_ORDER);
        if (empty($callMatches)) {
            return;
        }

        $this->writeDetectionHeader();
        $this->writeMatchList($callMatches);
    }




}