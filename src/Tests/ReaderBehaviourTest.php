<?php declare(strict_types=1);

namespace Miniature\Component\Tests;

use Miniature\Component\Component;
use Miniature\Component\InitParameters;
use Miniature\Component\Tests\TestEnv\Components\VerboseComponent;
use PHPUnit\Framework\TestCase;

require './autoload.php';

class ReaderBehaviourTest extends TestCase
{
    protected array  $configValues;
    protected string $dirname;
    protected string $originalText = 'original text';

    protected function SetUp() : void
    {
        $this->dirname = __DIR__ . '/TestEnv/config';
        $this->configValues = VerboseComponent::getInstance(
            (new InitParameters())
                ->setConfigDirectory($this->dirname)
                ->setEnv('dev')
                ->setAvailableEnv(['dev', 'do-not-scan'])
        )->getConfigValues();
    }

    protected function getMissingFieldMessage(string $offset, bool $isset, bool $isArray, bool $isNotEmpty)
    {
        return 'Expected field ' . $offset . ' to be an filled array but '.
               ($isset ? ($isArray ? ($isNotEmpty ? '' : 'it is empty.') : 'it is not an array.') : 'it is not set.');
    }


    public function mappingOffsetProvider()
    {
        return [
            ['di_mapping'],
            ['params'],
        ];
    }

    /**
     * @dataProvider mappingOffsetProvider
     */
    public function testAllMappingsExists($offset)
    {
        $isset      = isset(              $this->configValues[$offset]);
        $isArray    = $isset   ? is_array($this->configValues[$offset]) : false;
        $isNotEmpty = $isArray ? ! empty( $this->configValues[$offset]) : false;
        $boolean    = $isset && $isArray && $isNotEmpty;
        $this->assertTrue(
            $boolean,
            $this->getMissingFieldMessage( $offset,  $isset,  $isArray,  $isNotEmpty)
        );
    }



    public function classOffsetProvider()
    {
        return [
            ['class01'],
            ['class02_overridden_in_dev'],
            ['class03_overridden_in_subfolder'],
            ['class04_overridden_in_subfolder_dev'],
        ];
    }


    /**
     * @dataProvider classOffsetProvider
     */
    public function testAllDiMappingEntriesExist($offset)
    {
        $di_mapping = $this->configValues['di_mapping'];
        $isset      = isset(   $di_mapping[$offset]);
        $isArray    = is_array($di_mapping[$offset]);
        $isNotEmpty = ! empty( $di_mapping[$offset]);
        $boolean = $isset && $isArray && $isNotEmpty;
        $this->assertTrue(
            $boolean,
            $this->getMissingFieldMessage($offset, $isset, $isArray, $isNotEmpty)
        );
    }

    public function testClass01isNotOverridden()
    {
        $mapping = $this->configValues['di_mapping']['class01'];
        $string = $mapping['args']['string_param'];
        $this->assertEquals(
            $this->originalText,
            $string,
            "Expected ['di_mapping']['class01']['args']['string_param'] to be '$this->originalText' but it is '$string'."
        );
    }

    public function testClass02isOverriddenInDev()
    {
        $string   = $this->configValues['di_mapping']['class02_overridden_in_dev']['args']['string_param'];
        $expected = 'text overridden in dev';
        $this->assertEquals(
            $expected,
            $string,
            "Expected ['di_mapping']['class02_overridden_in_dev']['args']['string_param'] to be '$expected' but it is '$string'. \n" .
            "This most likelyy means the reader did not enter the 'dev' directory in '" . $this->dirname . "/dev'. \n"
        );
    }



    public function testClass03isOverriddenInSubfolder()
    {
        $string   = $this->configValues['di_mapping']['class03_overridden_in_subfolder']['args']['string_param'];
        $expected = 'text overridden in subfolder';
        $this->assertEquals(
            $expected,
            $string,
            "Expected ['di_mapping']['class03_overridden_in_subfolder']['args']['string_param'] to be '$expected' but it is '$string'. \n" .
            "This most likelyy means the reader did not enter the 'dev' directory in '" . $this->dirname . "/dev'. \n"
        );
    }


    public function testClass04isOverriddenInSubfolderDev()
    {
        $string   = $this->configValues['di_mapping']['class04_overridden_in_subfolder_dev']['args']['string_param'];
        $expected = 'text overridden in subfolder dev';
        $this->assertEquals(
            $expected,
            $string,
            "Expected ['di_mapping']['class04_overridden_in_subfolder_dev']['args']['string_param'] to be '$expected' but it is '$string'. \n" .
            "This most likelyy means the reader did not enter the 'dev' directory in '" . $this->dirname . "/dev'. \n"
        );
    }



}