<?php

namespace Miniature\Component;

use Miniature\Component\InitParametersInterface;
use Miniature\Component\Reader\DotEnv;
use Miniature\Component\Reader\Config;
use Miniature\Component\Reader\YamlParserDecoratorInterface;
use Miniature\Component\Reader\Value\ConfigParameters;
use Miniature\DiContainer\DiContainer;
use Miniature\DiContainer\Syntax\MapperAbstract as DiSyntaxMapperAbstract;
use Miniature\DiContainer\Syntax\MapperNative as DiSyntaxMapperNative;
use Miniature\Component\Coupling\TraceElement;
use Miniature\Component\Coupling\Supervisor;

/**
 * Class Component
 *
 * Abstract singleton class.
 *
 * Abstract in order to prevent direct instantiation.
 * You might need more Components than you might expect (multiiple components maybe).
 * Don't miss to give your heirs distinctive self-speaking names.
 *
 * Constructor-injection via Miniature\Component\InitParameters value object.
 * Override static::autoInject if you want to hide the injection.
 *
 * @package Miniature\Component
 * @author Guido Erfen <sourcecode@erfen.de>
 * @see https://github.com/guidoerfen/miniature-component#the-instance-of-the-component
 */
abstract class Component
{
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   MEMBERS
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    protected static ?Component              $instance            = null;
    private   ?InitParametersInterface       $params              = null;
    protected ?DiContainer                   $container           = null;
    private   ?DiSyntaxMapperAbstract        $diSyntaxMapper      = null;
    private   ?YamlParserDecoratorInterface  $yamlParserDecorator = null;
    private   ?Supervisor                    $couplingSupervisor = null;

    private ?string $configDirectoryPath     = null;
    private ?string $dotEnvFilePath          = null;
    private ?string $rootPath                = null;
    private ?string $env                     = null;

    private array   $envValues               = [];
    private array   $configValues            = [];
    private array   $possibleEnv             = ['dev', 'prod', 'test'];
    private array   $publicAccessAllowingEnv = ['dev', 'test'];

    protected function getProperties(): array
    {
        return [
            'env'                     => $this->env,
            'envValues'               => $this->envValues,
            'configValues'            => $this->configValues,
            'possibleEnv'             => $this->possibleEnv,
            'publicAccessAllowingEnv' => $this->publicAccessAllowingEnv,
        ];
    }


    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   INIT
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    public static function getInstance(InitParametersInterface $paramsObject = null) : Component
    {
        if (! static::$instance instanceof static) {

            if (! $paramsObject instanceof InitParametersInterface) {
                $paramsObject = static::autoInject();
            }
            static::$instance = new static($paramsObject);
        }
        return static::$instance;
    }

    private function __construct(InitParametersInterface $paramsObject = null)
    {
        $this->init($paramsObject);
    }

    final protected function __clone() {}

    private function init(InitParametersInterface $paramsObject = null)
    {
        if ($paramsObject instanceof InitParametersInterface) {
            $this->params = $paramsObject;
            $this->initPaths();
            $this->readEnv();
            $this->readOverrideParams();
            $this->readConfig();
            $this->initDiContainer();
            $this->initCouplingSupervisor();
        }
    }

    protected static function autoInject() : ?InitParametersInterface
    {
        return null;
    }





    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   COUPLING
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    protected function insureCoupling() : void
    {
        $trace = debug_backtrace();
        if (! isset($trace[1]) || !isset($trace[2])) {
            throw new \RuntimeException( "\n" .
                'Call of  ' . __METHOD__ .
                '() without an external call on public method being the occasion. ' . "\n" .
                'Please check the manual to learn about the use of this method.'
            );
        }

        $provider = new TraceElement($trace[1]);
        $consumer = new TraceElement($trace[2]);

        if (empty($consumer->getClass())) {
            throw new \RuntimeException( "\n"     .
                ' Public method call '                    . $provider->getClassMethodAsString() .
                ' not allowed for non-class-method functions ' . "\n"
            );
        }

        if (! $this->couplingSupervisor->insureCoupling($provider, $consumer)) {
            throw new \RuntimeException( "\n"     .
                ' Public method call '                    . $provider->getClassMethodAsString() .
                ' not allowed for external class method ' . $consumer->getClassMethodAsString() . "\n" .
                ' Please check the coupling wiring in '   . $this->configDirectoryPath . ".\n"
            );
        }
    }



    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   PATH
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    private function initPaths()
    {
        $this->rootPath            = $this->params->getAppRootPath();
        $this->configDirectoryPath = $this->params->getConfigDirectoryPath();
        $this->dotEnvFilePath      = $this->params->getDotEnvFilePath();
    }





    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   GET
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    private function publicAccessAllowed() : bool
    {
        if (! is_string($this->env)) {
            return false;
        }
        if (! in_array($this->env, $this->publicAccessAllowingEnv)) {
            return false;
        }
        return true;
    }

    private function throwExceptionIfNotPublicAccessAllowed() : void
    {
        if ($this->publicAccessAllowed()) {
            return;
        }
        $env     = is_null($this->env) ? 'NULL' : "'$this->env'";
        $allowed = implode('\', \'', $this->publicAccessAllowingEnv);
        $isAre   = count($this->publicAccessAllowingEnv) > 1 ? 'are' : 'is';
        throw new \RuntimeException(
            "\nNo public access allowed in the current environment which is $env. \n" .
            "Allowed $isAre: '$allowed' \n\n"
        );
    }

    private function getFromDiContainer(string $name) : ?object
    {
        $this->throwExceptionIfNotPublicAccessAllowed();
        if ($this->container instanceof DiContainer) {
            return $this->container->getFromPublic($name);
        }
        return null;
    }

    public function get(string $name) : ?object
    {
        return $this->getFromDiContainer($name);
    }

    public function __get(string $name) : ?object
    {
        return $this->getFromDiContainer($name);
    }





    /* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *   READ
     * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * */

    private function readOverrideParams()
    {
        $env = $this->params->getEnv();
        if (is_string($env) && ! empty($env)) {
            $this->env = $env;
        }

        $possibleEnv = $this->params->getPossibleEnv();
        if (is_array($possibleEnv)) {
            $this->possibleEnv = $possibleEnv;
        }

        $publicAccessAllowingEnv = $this->params->getEnvAllowingPublicAccess();
        if (is_array($publicAccessAllowingEnv)) {
            $this->publicAccessAllowingEnv = $publicAccessAllowingEnv;
        }

        $diSyntaxMapper = $this->params->getDiSyntaxMapper();
        if ($diSyntaxMapper instanceof DiSyntaxMapperAbstract) {
            $this->diSyntaxMapper = $diSyntaxMapper;
        }

        $yamlParserDecorator = $this->params->getYamlParserDecorator();
        if ($yamlParserDecorator instanceof YamlParserDecoratorInterface) {
            $this->yamlParserDecorator = $yamlParserDecorator;
        }
    }

    private function readEnv() : void
    {
        if (! is_file($this->dotEnvFilePath)) {
            return;
        }
        $dotEnv = new DotEnv($this->dotEnvFilePath);
        $this->envValues = $dotEnv->getValues();
        if (isset($this->envValues['APP_ENV'])) {
            $this->env = $this->envValues['APP_ENV'];
        }
    }

    private function readConfig() : void
    {
        if (! is_dir($this->configDirectoryPath)) {
            return;
        }
        $parameters = (new ConfigParameters())
            ->setDirectory($this->configDirectoryPath)
            ->setEnv($this->env)
            ->setAvailableEnv($this->possibleEnv)
            ->setYamlParserDecorator($this->yamlParserDecorator);
        $config = new Config($parameters);
        $this->configValues = $config->getValues();
    }

    private function initDiContainer()
    {
        if (! $this->diSyntaxMapper instanceof DiSyntaxMapperAbstract) {
            $this->diSyntaxMapper = new DiSyntaxMapperNative();
        }
        $this->container = (new \Miniature\DiContainer\DiContainer($this->diSyntaxMapper))
            ->readMappings($this->configValues);
    }

    private function initCouplingSupervisor()
    {
        $this->couplingSupervisor = new Supervisor($this->configValues);
    }

}