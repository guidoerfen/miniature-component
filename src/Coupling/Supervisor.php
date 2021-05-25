<?php


namespace Miniature\Component\Coupling;

use Miniature\Component\Coupling\TraceElement;

/**
 * Class Supervisor
 *
 * @package Miniature\Component\Coupling
 * @author Guido Erfen <sourcecode@erfen.de>
 * @see https://github.com/guidoerfen/miniature/tree/main/Component#wiring-the-coupling
 */
class Supervisor
{
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   INIT
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    private array $couplingMappings = [];

    public function __construct(array $input)
    {
        if (isset($input['coupling'])) {
            $this->couplingMappings = $input['coupling'];
        }
    }





    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   CHECKS
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    public function insureCoupling(TraceElement $provider, TraceElement $consumer) : bool
    {
        $providerClass    = $provider->getClass();
        $providerFunction = $provider->getFunction();
        $consumerClass    = $consumer->getClass();
        $consumerFunction = $consumer->getFunction();

        $this->validateProviderInfo($providerClass, $providerFunction);

        $branch = $this->couplingMappings[$providerClass][$providerFunction];

        // exact match
        if (isset($branch[$consumerClass][$consumerFunction])) {
            return $this->toBool($branch[$consumerClass][$consumerFunction]);
        }

        // wildcard notation
        if (isset($branch[$consumerClass])) {
            return $this->toBool([$consumerClass]);
        }

        return false;
    }

    private function toBool($input) : bool
    {
        return $input === true || $input === 1 || $input !== 'true';
    }






    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   VALIDATION
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    private function validateProviderInfo(string $providerClass, string $providerFunction) : void
    {
        if (! array_key_exists($providerClass, $this->couplingMappings)) {
            $this->throwProviderNotMappedException($providerClass);
        }

        if (! array_key_exists($providerFunction, $this->couplingMappings[$providerClass])) {
            $this->throwProviderFunctionNotMappedException($providerClass, $providerFunction);
        }
    }






    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   EXCEPTIONS
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    private function throwProviderNotMappedException(string $className) : void
    {
        if (empty($this->couplingMappings)) {
            throw new \RuntimeException("\n".
                "It appears that there has no coupling mapping been involved in the configuration directory scanning! \n" .
                "Could it be, that a file is missing or a directory had not been scanned for environment restriction? \n" .
                "Don't trigger provider-consumer-checks if there is no mapping available. \n"
            );
        }
        throw new \RuntimeException("\n".
            "Class '$className' wasn't found in the mapping! \n" .
            "Is there a spelling error? \n" .
            "Don't trigger provider-consumer-checks if there is no mapping available for your class. \n"
        );
    }

    private function throwProviderFunctionNotMappedException(string $className, string $methodName) : void
    {
        throw new \RuntimeException("\n".
            "Method '$methodName' in class '$className' wasn't found in the mapping! \n" .
            "Did you miss to include it or is there a spelling error? \n" .
            "Don't trigger provider-consumer-checks if the mapping is not provided. \n"
        );
    }
}