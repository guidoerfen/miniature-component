<?php
declare(strict_types=1);

namespace Miniature\Component\Coupling;

/**
 * Class TraceElement
 *
 * A transfer/parameter object providing reduced info from the backtrace
 *
 * @package Miniature\Component\Coupling
 * @author Guido Erfen <sourcecode@erfen.de>
 * @see https://github.com/guidoerfen/miniature-component#wiring-the-coupling
 */
class TraceElement
{
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    private string $class;
    private string $function;
    private int    $line;

    public function __construct(array $traceData)
    {
        $this->class    = isset($traceData['class'   ]) ? $traceData['class'   ] : '';
        $this->function = isset($traceData['function']) ? $traceData['function'] : '';
        $this->line     = isset($traceData['line'    ]) ? (int) $traceData['line'    ] : '';
    }

    public function __get(string $member) : ?string
    {
        if (isset($this->$member)) {
            return $this->$member;
        }
        return null;
    }


    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    public function getClass()    : string
    {
        return $this->class;
    }

    public function getFunction() : string
    {
        return $this->function;
    }

    public function getLine()     : string
    {
        return $this->line;
    }

    public function getClassMethodAsString() : string
    {
        return $this->class . '->' . $this->function . '()';
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    public function methodEquals(?string $class, string $function) : bool
    {
        return $class === $this->class && $function === $this->function;
    }

}