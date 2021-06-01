<?php declare(strict_types=1);

namespace Miniature\Component\Tests;

require './autoload.php';

use Miniature\Component\Component;
use Miniature\Component\InitParameters;
use Miniature\Component\Tests\TestEnv\Components\AComponent;
use Miniature\Component\Tests\TestEnv\Components\BComponent;
use PHPUnit\Framework\TestCase;


class InitParametersTest extends TestCase
{
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *               CONF
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    public function testConfPathIsFound()
    {
        $dirname = __DIR__ . '/TestEnv/config';
        $paramObject = (new InitParameters())
            ->setConfigDirectory($dirname);
        $this->assertIsString(
            $paramObject->getConfigDirectoryPath(),
            "\n" .
            "Failed to read the configuration directory. \n" .
            "Tried: '$dirname'"
        );
    }

    public function testFalseConfPathThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $dirname = '/nonsense';
        $paramObject = (new InitParameters())
            ->setConfigDirectory($dirname);
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *               DotENV
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    public function testDotEnvPathIsFound()
    {
        $dirname = __DIR__ . '/TestEnv/dotEnvDirectory';
        $paramObject = (new InitParameters())
            ->setDotEnvPath($dirname);
        $this->assertIsString(
            $paramObject->getDotEnvFilePath(),
            "\n" .
            "Failed to read the dot-env directory. \n" .
            "Tried: '$dirname'"
        );
    }

    public function testFalseDotEnvPathThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $dirname =  __DIR__ . '/TestEnv/config';;
        (new InitParameters())->setDotEnvPath($dirname);
    }

    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
}