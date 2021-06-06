<?php


namespace Miniature\Component\Reader\Logger;


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
        $blank       = substr($this->blank, 0, $length);
        $intoLength  = strlen($writeInto) - $length;
        $isOdd       = $intoLength % 2;
        $blankLength = floor($intoLength / 2);
        $startBlank  = substr($writeInto, 0, $blankLength);
        $endBlank    = ($isOdd ? ' ' : '') . substr($writeInto, $blankLength * -1);

        for ($i = 0; $i < count($parts); $i++) {
            $string = $parts[$i];
            $blankLength = $length - strlen($string);
            $temp        = $parts[$i] . substr($blank, 0, $blankLength);
            $parts[$i]   = $startBlank . $temp . $endBlank;
        }
        return $parts;
    }


    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *                                WRITE
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    public function writeLine(string $string)
    {
        echo $string . "\n";
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
}