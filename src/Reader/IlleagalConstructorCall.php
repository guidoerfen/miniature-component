<?php

namespace Miniature\Component\Reader;

use Miniature\Component\Component;
use Miniature\Component\Reader\Value\ConstructorCallDetector;
use Miniature\Component\Reader\Value\IlleagalConstructorCallParameters;
use Miniature\DiContainer\DiContainer;
use Miniature\Component\Reader\Logger\IlleagalConstructorCallLogger;

class IlleagalConstructorCall
{
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *    INIT
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    const FILE_PATH_KEY = 'filePath';

    private Component   $component;
    private DiContainer $diContainer;
    private IlleagalConstructorCallLogger $logger;
    private string      $rootPath;
    private array       $excludeDirectories = ['vendor' => true];
    private string      $libInstallationPath;

    private array       $mapping;
    private int         $errorCount = 0;
    private array       $supportedFileNameSuffixes = ['html', 'twig', 'volt', 'blade', 'smarty'];

    private function getRealPath(string $path)   : string
    {
        return str_replace('\\', '/', realpath($path));
    }

    public function __construct(IlleagalConstructorCallParameters $parameters)
    {
        $this->diContainer         = $parameters->getDiContainer();
        $this->rootPath            = $parameters->getRootPath();
        $this->excludeDirectories  = $parameters->getExcludeDirectories();
        $this->logger              = $parameters->getLogger();
        $this->component           = $parameters->getComponent();
        $this->mapping             = $this->diContainer->getClassRegExMapping();
        $this->libInstallationPath = $this->getRealPath(__DIR__ . '/../../..');
    }







    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *    RUN
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    public function run() : int
    {
        $this->logger->writeHeader("\n" .
            "Detecting: \n- Violations concerning Component:\n  " .
            get_class($this->component) . "\n"
        );
        $this->logger->writeLine('');

        $this->readDir($this->rootPath);

        $this->logger->writeLine('');
        $this->logger->writeDSecondLevel(
            "Violations found: $this->errorCount"
        );
        $this->logger->writeLine('');
        $this->logger->writeHeader("\n" .
            "E N D Detecting for Component: \n- " .
            get_class($this->component) . "\n"
        );
        $this->logger->writeLine('');
        $this->logger->writeLine('');

        return $this->errorCount;
    }







    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *     READ
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    private function readDir($path) : void
    {
        $list = scandir($path);
        array_shift($list); // remove '.'
        array_shift($list); // remove '..'

        foreach ($list as $filename) {
            if (! $this->fileNamePatternQualifies($filename)) {
                continue;
            }
            $filepath = str_replace('//', '/', $path.'/'.$filename);
            if (is_dir($filepath) && $this->dirNameQualifies($filename, $filepath)) {
                $this->readDir($filepath);
                continue;
            }
            if (is_file($filepath) && $this->fileNameQualifies($filename)) {
                $this->readFile($filepath, $filename) ;
            }
        }
    }

    private function readFile($filepath, $filename)
    {
        $content = file_get_contents($filepath);
        if (strpos($content, '<?php') === false) {
            return;
        }
        foreach ($this->mapping as $params) {
            $params[self::FILE_PATH_KEY] = $filepath;
            $detector = new ConstructorCallDetector($content, $params, $this->logger, $this);
            $this->errorCount += $detector->detect();
        }
    }








    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *     FILTER /QUALIFY
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    private function fileNamePatternQualifies($filename) : bool
    {
        $firstDotPos = strpos($filename, '.');
        if ($firstDotPos === false) {
            return true;
        }
        if ($firstDotPos === 0) {
            return false;
        }
        return true;
    }

    private function dirNameQualifies($dirname, $dirpath) : bool
    {
        $name = strtolower($dirname);
        if ($dirpath === $this->libInstallationPath) {
            return false;
        }
        if (isset($this->excludeDirectories[$name])) {
            return false;
        }
        return true;
    }

    private function fileNameQualifies($filename) : bool
    {
        $extension = substr($filename, (strrpos($filename, '.')) + 1);
        if (substr($extension, 0, 2) === 'ph') {
            return true;
        }
        if (in_array($extension, $this->supportedFileNameSuffixes)) {
            return true;
        }
        return false;
    }


}