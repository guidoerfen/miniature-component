<?php
declare(strict_types=1);

namespace Miniature\Component\Reader\Logger;

/**
 * Class IlleagalConstructorCallLogger
 * @package Miniature\Component\Reader\Logger
 * @author Guido Erfen <sourcecode@erfen.de>
 */
class IlleagalConstructorCallLogger
{
    private $firstLevelLine   = '* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *';
    private $firstLevelBlank  = '*                                                                                                   *';
    private $blank            = '                                                                                                     ';

    private $secondLevelLine  = '* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *';
    private $secondLevelBlank = '*                                                                         *';

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *                                EQUAL STRING
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    private  function getLongestStringLength(array $input) : int
    {
        $length = 0;
        for ($i = 0; $i < count($input); $i++) {
            $temp = strlen($input[$i]);
            if ($temp > $length) {
                $length = $temp;
            }
        }
        return $length;
    }

    private  function fillToEqualLength(string $input, string $writeInto) : array
    {
        $parts       = explode("\n", $input);
        $length      = $this->getLongestStringLength($parts);
        $writeInto   = $this->optimizeWriteInto($writeInto, $length);
        $blank       = substr($this->blank . $this->blank. $this->blank, 0, $length);
        $intoLength  = strlen($writeInto) - $length;
        $isOdd       = $intoLength % 2;
        $blankLength = (int) floor($intoLength / 2);
        $startBlank  = substr($writeInto, 0, $blankLength);
        $endBlank    = ($isOdd ? ' ' : '') . substr($writeInto, $blankLength * -1);

        for ($i = 0; $i < count($parts); $i++) {
            $string          = $parts[$i];
            $currBlankLength = $length - strlen($string);
            $temp            = $string . substr($blank, 0, $currBlankLength);
            $parts[$i]       = $startBlank . $temp . $endBlank;
        }
        return $parts;
    }

    private function optimizeWriteInto(string $writeInto, int $longestLength) : string
    {
        $targetLength = strlen($writeInto);
        if ($longestLength < $targetLength) {
            return $writeInto;
        }
        $difference = $longestLength - $targetLength + 10;
        $writeInto  = substr($writeInto, 0, 20) . substr($this->blank, 0, $difference) . substr($writeInto, 21);
        return $writeInto;
    }


    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *                                WRITE
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    public function writeLine(string $string) : void
    {
        echo $string . "\n";
    }

    public function write(string $string) : void
    {
        echo $string;
    }


    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *                                HEADER
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    public function writeHeader(string $string) : void
    {
        $parts = $this->fillToEqualLength($string, $this->firstLevelBlank);
        $this->writeLine($this->firstLevelLine);
        for ($i = 0; $i < count($parts); $i++) {
            $this->writeLine($parts[$i]);
        }
        $this->writeLine($this->firstLevelLine);
    }

    public function writeDSecondLevel(string $string) : void
    {
        $parts = $this->fillToEqualLength($string, $this->secondLevelBlank);
        $this->writeLine($this->secondLevelLine);
        for ($i = 0; $i < count($parts); $i++) {
            $this->writeLine($parts[$i]);
        }
        $this->writeLine($this->secondLevelLine);
    }


    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *                                Block
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    public function writeBlock(string $string, string $pre = '') : void
    {
        $parts       = explode("\n", $string);
        for ($i = 0; $i < count($parts); $i++) {
            $this->writeLine($pre . $parts[$i]);
        }
    }
}