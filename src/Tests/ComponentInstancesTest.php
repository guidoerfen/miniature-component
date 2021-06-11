<?php declare(strict_types=1);

namespace Miniature\Component\Tests;

require './autoload.php';

use Miniature\Component\Component;
use Miniature\Component\InitParameters;
use Miniature\Component\Tests\TestEnv\Components\AComponent;
use Miniature\Component\Tests\TestEnv\Components\BComponent;
use PHPUnit\Framework\TestCase;


/**
 * Class ComponentInstancesTest
 * @package Miniature\Component\Tests
 * @author Guido Erfen <sourcecode@erfen.de>
 */
class ComponentInstancesTest extends TestCase
{
    public function testInstnatiationOfInheritor()
    {
        $aComponent = AComponent::getInstance();
        $classIsExpectedInstance =  ($aComponent instanceof AComponent);
        $this->assertTrue(
            $classIsExpectedInstance, "\n" .
            "Error in inheritance mechanism of  Miniature\Component\Component: \n" .
            "expected instance of AComponent. \n" .
            "Got " . gettype($aComponent). " \n" .
            "Check any changes in property \$instance. \n" .
            "Override in inheritors must guarantee that exactly the unique instance \n" .
            "inheritor is stored, not anything else."
        );
    }

    public function testUniqueInstances()
    {
        $aComponent = AComponent::getInstance();
        $bComponent = BComponent::getInstance();
        $classesAreNotSameInstances = $aComponent !== $bComponent;
        $this->assertTrue(
            $classesAreNotSameInstances, "\n" .
            "Error in inheritance mechanism of  Miniature\Component\Component: \n" .
            "expected instances of AComponent and BComponent. \n" .
            "Got " . get_class($aComponent) . " and " . get_class($bComponent) . "\n" .
            "Check any changes in property \$instance. \n" .
            "Override in inheritors must guarantee that exactly the unique instance \n" .
            "inheritor is stored, not anything else."
        );
    }




}