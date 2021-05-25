# Component
### Warning!
This is still on an experimental level.
We don't know if we've got everything together in the moment.
Extensive testing needs to be done also.

### Purpose
This is a template for a **component of PHP-classes**
provided by a [**dependency injection container**](#the-di-mapping) (DI-Container).
The container itself is hidden by the [**component instance**](#the-instance-of-the-component) (**black box approach**)
which from the outside serves as a [facade](#coupling-detection-and-protection).

As a facade, the component is provided with a mechanism of access restriction.
This access restriction in return ist the wiring of [**the component coupling**](#wiring-the-coupling)
and the much-needed bottleneck regarding the communication of the classes
residing in the container to the outer world.

The behaviour can be changed easily by the help of certain injections and overrides.
The DI-Container is an independent package of its own.

### What does "Component" mean in this context?
We are seeking for a toolset to build a composite component architecture.
We are trying to build a tiny framework, that enables us to set up something like this relatively easily.

![component architecture](img/TwoComponents.png)

If this does not look familiar to you and if the
[Cohesion & Coupling Problem](https://en.wikipedia.org/wiki/Coupling_(computer_programming))
is not on your agenda
it is likely that you are using a different terminology
and that this package is absolutely not for you.

### The basic steps of setting up a component:
- Inheritance from the `Miniature\Component\Component` Singleton and giving that a **distinctive self speaking name**
- Setting up a **configuration folder** and injecting the directory path information to the Component
  - Providing configuratuion-files (PHP-array or YAML) ...
    - that contain the **dependency injection wiring** and
    - the **component coupling wiring**
- And you are ready to go





<div>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;</div>

# Installation
### Using Composer

```shell script
composer require miniature/component
```
### Downloading Package

You'll also need the
[**DI-Container package**](https://github.com/guidoerfen/miniature-di_container).

Unzip both to a directory named **`Miniature`**.
Add to your autoloading something like the following:

```PHP
<?php

function miniature_autoload($class)
{
    $fileName = str_replace('\\', '/', realpath(__DIR__) . '/' . $class ) . '.php';
    if (preg_match('/^(.*\/Miniature)\/(\w+)\/((\w+\/)*)(\w+)\.php/', $fileName)) {
        $newFileName = preg_replace(
            '/^(.*\/Miniature)\/(\w+)\/((\w+\/)*)(\w+)\.php/',
            '$1/$2/src/$3$5.php',
            $fileName
        );
        if (is_file($newFileName)) {
            require $newFileName;
        }
    }
}
spl_autoload_register('miniature_autoload');

```

Can be that you must adjust the file path concatenation for `filePath`
by setting the relative path in the `filepath()` statement.




<div>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;</div>

# The instance of the Component
### The basic instantiating
The basic class is an abstract Singleton.
The purpose of this is:
- that your component is considered something unique in your PHP environment
- there will be multiple components
- you components shall be globally addressable at any time

Having said this and having made up your own name,
you  might almost be done with a few lines like this:

```PHP
<?php

use Miniature\Component\Component;

class SelfSpeakingComponent extends Component {    
    protected static ?Component $instance = null;
}

$selfSpeakingComponentInstance = SelfSpeakingComponent::getInstance();
```
Most important here is the `protected static $instance` property.
It ensures that always the same instance of the inheritor you create is used.
Otherwise you will be confronted with unexpected behaviour as soon as you work with multiple component instances
while everything seemed fine with a unique instance.

This will work without errors, but it will not do anything.


### Parameter injecting: Path to the configuration directory
The Component instance needs to know where to [read the configurations](#configuration-reading)<!-- @IGNORE PREVIOUS: anchor -->.
And pretty likely there is more information and data the component is to be fed with.
Therefore there is a parameter-class `Miniature\Component\InitParameters` to [inject the component constructor](#parameter-injection-in-general) with.
Here in this first example the path to the configuration directory might do.

```PHP
$paramObject = (new Miniature\Component\InitParameters())
    ->setConfigDirectory( __DIR__ . '/../config');
$selfSpeakingComponentInstance = SelfSpeakingComponent::getInstance($paramObject);
```

This may not look good for you, since because you don't know where the component is called first
in the lifetime of a request handling. Therefore, there is a overridable static auto-injection method.
<a name="auto-injection"></a>
```PHP
<?php

use Miniature\Component\Component;
use Miniature\Component\InitParametersInterface;
use Miniature\Component\InitParameters;

class SelfSpeakingComponent extends Component 
{
    protected static ?Component $instance = null;
    
    protected static function autoInject() : ?InitParametersInterface
    {
        return (new InitParameters())
            ->setConfigDirectory( __DIR__ . '/../config');
    }
}
```
Learn about the content of the configuration directory and the dependeny wiring
[here](#reading-the-configuration-directory).

### Your almost finished component
Given, you want to provide a type-save respectively contract-save access to chosen
instances that live in the DI-Container, your finished component might look
somewhat like this:

```PHP
<?php

namespace YourOwnExamleApp;

use Miniature\Component\Component;
use Miniature\Component\InitParametersInterface;
use Miniature\Component\InitParameters;

use YourOwnExamleApp\The2ndComponent;
use YourOwnExamleApp\ProductAccessInterface;
use YourOwnExamleApp\PersonAccessInterface;
use YourOwnExamleApp\AddressAccessInterface;

class SelfSpeakingComponent extends Component 
{
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *                 INIT
     * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    protected static ?Component $instance = null;
    
    protected static function autoInject() : ?InitParametersInterface
    {
        return (new InitParameters())
            ->setConfigDirectory( __DIR__ . '/../config');
    }
    
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *                 PROVIDE
     * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    public function providePerson() : PersonAccessInterface
    {
        return $this->container->get('person_access');
    }
    
    public function provideAddress() : AddressAccessInterface
    {
        return $this->container->get('address_access');
    }
         
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *                 CONSUME
     * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    public function consumeProduct() : ProductAccessInterface
    {
        return The2ndComponent::getInstance()->provideProduct();
    }
}
```

There are two public methods which provide certain access by the means of objects
they retrieved from the DI-Container.
There is one method retrieving another access object from another component,
most likely returning it to a class residing inside the component,
thus consuming the contract.

But you're not really done yet.
Though you provided a contract by returning an interface implementation in your public methods
you don't have the control about "who" is consuming it.
Without any controll it very easily will result in multiple criss-cross connection spun
among the components and thus a very undesrired structure. A failure of the architectural task.

Also, some restrictions about which classes from inside the DI-Container will make sense.

Threfore, `Miniature\Component` offers a [simple wiring mechanism](#wiring-the-coupling).


### Coupling detection and protection

 `Miniature\Component\Component` offers a protection method that ensures that it is only accessed
 by classes and methods having been granted to do so by
 [wiring the coupling configuration](#wiring-the-coupling).
 No return value needed. `insureCoupling()` very simply throws Exceptions.
```PHP
   $this->insureCoupling();
```
Find the providing and consuming methods in the example below provided with the call.
Without [the wiring](#wiring-the-coupling) they will do nothing but throw Exceptions.

```PHP
<?php

namespace YourOwnExamleApp;

use Miniature\Component\Component;
use Miniature\Component\InitParametersInterface;
use Miniature\Component\InitParameters;

use YourOwnExamleApp\The2ndComponent;
use YourOwnExamleApp\ProductAccessInterface;
use YourOwnExamleApp\PersonAccessInterface;
use YourOwnExamleApp\AddressAccessInterface;

class SelfSpeakingComponent extends Component 
{
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *                 INIT
     * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    protected static ?Component $instance = null;
    
    protected static function autoInject() : ?InitParametersInterface
    {
        return (new InitParameters())
            ->setConfigDirectory( __DIR__ . '/../config');
    }
    
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *                 PROVIDE
     * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    public function providePerson() : PersonAccessInterface
    {
        $this->insureCoupling();
        return $this->container->get('person_access');
    }
    
    public function provideAddress() : AddressAccessInterface
    {
        $this->insureCoupling();
        return $this->container->get('address_access');
    }
        
    /* * * * * * * * * * * * * * * * * * * * * * * * * * * *
     *                 CONSUME
     * * * * * * * * * * * * * * * * * * * * * * * * * * * */
    public function consumeProduct() : ProductAccessInterface
    {
        $this->insureCoupling();
        return The2ndComponent::getInstance()->provideProduct();
    }
}
```






<div>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;</div>

<a name="di-mapping"></a>
# Wiring the Coupling

These wiring examples are roughly based on the component-class example
[shown above](#coupling-detection-and-protection).
We just assume there is a `The2ndComponent`and some classes from the inside of the container.
The examples are simplified.
Of course the class names would appear in their fully qualified form here.

Be aware that [YAML-support is not available by default](#yaml-support).

<table style="border:0;width:100%;"><tr><td> PHP </td><td> YAML </td></tr>
<tr><td style="padding:0;">

```PHP
<?php
return [
    'SelfSpeakingComponent' => [
        'providePerson' => [
            'The2ndComponent' => [
                    'consumeAPerson' => true
            ]
        ],
        'provideAddress' => [
            'The2ndComponent' => [
                    'consumeAdress' => true
            ]
        ],
        'consumeProduct' => [
            'SomeClassFromInsideOfTheContainer' => [
                    'fetchProduct' => true
            ],
            'SomeOtherClassFromInsideOfTheContainer' => [
                    'retrieveProduct' => true,
                    'evaluateProduct' => true
            ],
            'ClassOnlyInTestingEnvironment' => true
        ],
    ],
    'The2ndComponent' => [
        'provideProduct' =>[
            'SelfSpeakingComponent' => [
                    'consumeProduct' => true
            ]
        ]
    ],
    'Unfinished3rdComponent' => [
        'provideTonsOfInfo' =>[
            'The2ndComponent' => true
        ]
    ]
];
```

</td>
<td style="padding:0;vertical-align:top;" valign="top">

```YAML
coupling:
    SelfSpeakingComponent:
        providePerson:
            The2ndComponent:
                consumePerson: true
        provideAddress:
            The2ndComponent:
                consumeAdress: true
        consumeProduct:
            SomeClassFromInsideOfTheContainer:
                fetchProduct: true
            SomeOtherClassFromInsideOfTheContainer:
                retrieveProduct: true
                evaluateProduct: true
            ClassOnlyInTestingEnvironment: true
    The2ndComponent:
        provideProduct:
            SelfSpeakingComponent:
                consumeProduct: true
    Unfinished3rdComponent:
        provideTonsOfInfo:
             The2ndComponent: true
```

</td>
</tr>
</table>


#### The first wiring in the example explained

Start of the component coupling section:
```YAML
coupling:
```
The method `SelfSpeakingComponent->providePerson()` ...
```PHP
   SelfSpeakingComponent::getInstance()->providePerson();
```
```YAML
    SelfSpeakingComponent:
        providePerson:
```
... allows to be called by `The2ndComponent->consumePerson()`.
```PHP
   The2ndComponent::getInstance()->consumePerson();
```
```YAML
            The2ndComponent:
                consumePerson: true
```
This can be switched off immediately by changing the value to **`false`**.

#### Gerneral wiring features

Generally, there are assignments of multiple classes and multiple methods possible.
It should become clear by watching the YAML structure.

Multiple assignments among components should be considered undesirable
unless it is proven, that these assignments altogether realize somewhat **"one interface"**.
It might be different for classes that access the component from inside the DI-Container.

#### Wildcard notation

There is also the option, to grant a class in general.
Maybe for testing or developement or it is a class residing inside the container
dedicated to the communication with the component.
```YAML
            OnlyInTestingEnvironment: true
```








<div>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;</div>



<a name="di-mapping"></a>
# The DI-Mapping

#### Hard wiring only
Sorry, so far there will be no auto-wiring.
Beside lack of developement time and possible ressource issues caused by the use of reflection classes
we also think that, as far as interface-to-implementation mapping is concerned,
you have all the means at hand already.

#### Deadlock Protection
`Miniature\DiContainer` provides a deadlock protection via the `Miniature\DiContainer\DiNode`-tree
that is built in the instantiation process.
A testing mode that bubble the whole wiring might be desired for the future.

## An example
Let's start with simple examples in PHP and YAML.
Be aware that [YAML-support is not available by default](#yaml-support).
PHP on the other hand might have the advantage (or uncertainty) that structures can be produced dynamically.

If you used to service-container configuarations in one of the popular MVC frameworks,
this might look familiar to you.
Indeed, we had considered adapting the syntax from Symfony but came to the conclusion
that the use of the key `service` causes much confusion in the terminology.
So mapping-branch it is named as what it represents: `di_mapping`.
If you want to change the syntax you can do it by [overriding](https://github.com/guidoerfen/miniature-di_container#syntax-overrides).


<table style="border:0;width:100%;"><tr><td> PHP </td><td> YAML </td></tr>
<tr><td style="padding:0;">

```PHP
<?php
return [
    'di_mapping' => [
        'person' => [
            'class'      => 'AppDemo\Person',
        ],
        'address' => [
            'class'      => 'AppDemo\Address',
            'args'       => [ '@person' ]
        ],
        'person_manager' => [
            'class'      => 'AppDemo\PersonManager',
            'args'       => [
                'person'     => '@person',
                'address'    => '@address',
                'db_wrapper' => '@mysql_wrapper'
            ]
        ],
        'mysql_wrapper'  => [
            'class'      => 'AppDemo\MysqlWrapper',
            'singleton'  => true,
            'args'       => [
                '%mysql_person_connection',
                '%access_token'
            ]
        ],
        'person_access'  => [
            'class'      => 'AppDemo\PersonAccess',
            'static'     => 'getInstance',
            'public'     => true,
            'args'       => [
                '@miniature.di_container'
            ]
        ],
    ],

    'params' => [
        'mysql_person_connection' => [
            'host'       => '127.0.0.1',
            'port'       => 3306,
            'user'       => 'root',
            'pw'         => 'top-secret-123',
            'dbName'     => 'person',
            'class'      => 'Component\DbMysql'
        ],
        'access_token'   => 'xOQvvkE2NGsaoUPJqlwpuzP'
    ]
];
```

</td>
<td style="padding:0;vertical-align:top;" valign="top">

```YAML


di_mapping:
    person:
        class: AppDemo\Person
    address:
        class: AppDemo\Address
        args:
          - '@person'
    person_manager:
        class:
        args:
            person: '@person'
            address: '@address'
            db_wrapper: '@mysql_wrapper'
    mysql_wrapper:
        class: AppDemo\MysqlWrapper
        singleton: true
        args:
          - '%mysql_person_connection'
    person_access:
        class: AppDemo\PersonAccess
        static: getInstance
        public: true
        args:
          - '@miniature.di_container'
params:
    mysql_person_connection:
        host: 127.0.0.1
        port: 3306
        user: root
        pw: top-secret-123
        dbName: person
    access_token: xOQvvkE2NGsaoUPJqlwpuzP
```

</td>
</tr>
</table>




<a name="di-mapping-keys-explained"></a>
## The keys explained
#### Keys for classes
```YAML
          - '@person'
```
Find the 2nd-level keys such as `person`, `address`, `person_manager`, `mysql_wrapper`,
and `person_access` repeated in the `args` sub-branches with an `@`-prefix added.
This is how classes are mapped as arguments (`args`) for the constructor injection.
The naming is completely your choice. Consider namespacing.
As mentioned above, equal keys will lead to overrides.
This is for the realisation of [environment-based overrides](#environment-based-overrides).

Compulsory for each class-mapping is the key `class` that most likely refers to a fully qualified class name.

#### Keys for parameter data
```YAML
          - '%mysql_person_connection'
```
Find the `mysql_wrapper` attributed with an argument tagged `%mysql_person_connection`.
You will find this key (`mysql_person_connection`) down in the `params`-branch.
This will be passed to the constructor without any changes.
The same is true about the key `access_token` (`%access_token` respectively).

#### Key of the DI-Container
```YAML
          - '@miniature.di_container'
```
The mapping `person_access` bears an example of how to inject the DI-Container itself via the arguments list:
`@miniature.di_container`.

Be aware constructor injecting is easier to handle for the purpose of unit tests.
But there are cases likely where you will even need it since this is the only way to directly access the container.

#### Key: 'static'
```YAML
        static: getInstance
```
Another example from the entry `person_access`: the key `static`.

This should bear the string with the name of a static generation-/access-method to be used instead of the average constructor call (e.g. `new ClasName($param)` ).
In the case of our example it is the standard call for a singleton pattern, which will result in:

```PHP
    $instance = AppDemo\PersonAccess::getInstance($container);
```

#### Key: 'singleton'
```YAML
        singleton: true
```
The use of the key `singleton` is completely independent from the design pattern of the same name.
By assigning a **`true`**  you force the class only to be instantiated once.
This is not only useful for the cases where you would use the design pattern.
This might always be useful in cases when you can be ensured that no results will be stored in member variables.

This is the fastest and ressource saving way to access an instance.
An interal key `instance` always will be checked.
If there is content it will be returned and all other proceedings will be skipped.
To be true, this is not a substutute for a real singleton pattern.
You could instantiate the same class under two different keys, maybe using different parameters.
Maybe this is your intention?

#### Key 'public'
```YAML
        public: true
```
Setting the key `public` to **`true`** you make the instance available from the outside via the component.
By default, this functionality is available for [environments](#configuring-environments) 'dev' and 'test' only.
You can control the behaviour by setting the environments that allow `public`.


```PHP
$paramObject = (new Miniature\Component\InitParameters())
    // other configurations
    ->setEnvAllowingPublicAccess('dev', 'test', 'prod');
```


In our case something like this (assuming we are using [auto-injection](#auto-injection)<!-- @IGNORE PREVIOUS: anchor -->):
```PHP
SelfSpeakingComponent::getInstance()->person_access->somePublicMethod('string_parameter');
```
In case you dislike magic access you might prefer `get($string)` as shown here:

```PHP
SelfSpeakingComponent::getInstance()->get('person_access')->somePublicMethod('string_parameter');
```
If you feel tempted to declare everything `public` or question what this cumbesersome stuff is for,
I'd suggest to gain info about the Cohesion & Coupling problem and what component architectures are about.



#### Overriding arguments on the fly

Please note: This feature is not compatible with the [singleton](#key-singleton) feature.
An attempt to combine both features will result in an Exception.

This kind of overriding the arguments is only available to classes
which had the container injected.
Retrieving instances from the container works via the `get`:
```PHP
$instanceFromMapping = $this->container->get('class_key_string');
```
On the fly overriding happens via a second parameter, an array whose fields are indexed the same as the original arguments array.
You might provide all parameters once again or you might just want to provide cerrtain parameters.
In the second case string-indexes in the arguments are useful.

Considering the excaple from above, let's assume the `person_access`-instance
wants to retrieve a `person_manager` instance using replacement-classes
for `@person` and `@adress`, it might look somehow like this:
```PHP
$instanceWithOverrides = 
    $this->container->get(
        'person_manager',
        [
            'address' => '@other_address_key',
            'person'  => '@other_person_key',
        ]
    );
```
Since the `person_manager` hold an associative array as arguments, there is even no need to care about the sequence.
This approach would also work with numeric keys basically, but would one fell comfortable with that?


<div>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;</div>







<a name="configuration-reading"></a>
# Reading the configuration directory
<a name="reading-behavour"></a>
## Reading behaviour
All files in the config-directory and it's sub-directories recursively will be read,
given that the format is supported. Currently that is:
- **PHP**: The PHP-files always should return an array
- **YAML**: This is depending on the PECL-extension for YAML is loaded. Alternatively the component class can be injected with a decorator that holds the PHP-based YAML-interpreter of youir choice. More about this [here](#yaml-support)

#### 1st recursion level: selecting the interpreter
There are three main keys for the three main purposes: `di_mapping`, `params`, and `coupling`.
- **`di_mapping`** contains the actual [mapping of classes](#the-di-mapping), injected parameters and behavoiour.
- **`params`** contains array data which are [adressable as parameters](#keys-for-parameter-data) but will not be interpreted at all.
- **`coupling`** contains the wiring that provides the [component coupling](#wiring-the-coupling)

*(By the way: If you don't like the naming you can change it by incection of the DI-Conatiner, ecept for the naming of the `coupling` key.)*

#### 2nd recursion level: access keys to classes and paarameters
The keys on the next recursion level are all your choice.
These are the names your classes and Parameters will be addressed with.
Content with equal keys will be overwritten.
This is for the basic mechanism that enables us to
[override for the needs of a certain environment](#environment-based-overrides).

<a name="yaml-support"></a>
## YAML Support
YAML is not part of the standard PHP installation.
On the other hand we try hard to keep Miniature free of foreign dependency.
Nevertheless for all the configuration work YAML is very much desireable.

Therefore, currently there are two choices:
- Installing the [PECL-extension for YAML](https://pecl.php.net/package/yaml)
- Injecting a decorator for an external PHP-based YAML-parser implementing the `Miniature\Component\Reader\YamlParserDecoratorInterface`

The config-reader will detect if the YAML-PECL extension is installed and will give it the preference.
If not, it will look if the decorator is injected and use this.
Otherwise, it will throw a warning level error in case it meets a YAML file without any parser available.

We assume, the [YamlParserDecoratorInterface](https://github.com/guidoerfen/miniature/blob/main/Component/src/Reader/YamlParserDecoratorInterface.php)
speaks for itself.
The [InitParameters](#parameter-injection-in-general)-class offers a dedicated setter for it:

```PHP
$paramObject = (new Miniature\Component\InitParameters())
    // other settings
    ->setYamlParserDecorator(new MyOwnAPP\YamlParserDecorator());
```


<a name="env-based-overrides"></a>
## Environment based overrides
As [described above](#reading-behaviour) the configuration reader is merciless in overriding 2nd-recursion-level keys.
And it will also continue so, by reading every sub-folder of the
[configuration directory](#setconfigdirectory) recursively.
You can change that by listing the sub-directories in the
[available environments](#setting-available-environments).
The inclusion in this list will prevent the eqully named directories from being read
unless [the current environment](#current-environment)
happens to be of the same name as the directory.

Thus is the implementation of the environment based overrides.
Nothing more.





<div>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;<br>&nbsp;</div>






# Configuring environments
## Parameter injection in general
The basic configuration is done via a parameter object that is passed to the Container instance.

```PHP
$paramObject = (new Miniature\Component\InitParameters())
    ->setConfigDirectory( __DIR__ . '/../config');
$selfSpeakingComponentInstance = SelfSpeakingComponent::getInstance($paramObject);
```
In longer terms the auto-injection is the better choice:

```PHP
class SelfSpeakingComponent extends Component 
{
    protected static ?Component $instance = null;
    
    protected static function autoInject() : ?InitParametersInterface
    {
        return (new InitParameters())
            ->setConfigDirectory( __DIR__ . '/../config');
    }
}
```
InitParameters supports method-chaining:
```PHP
$paramObject = (new Miniature\Component\InitParameters())
    ->setAppRootPath(__DIR__ . '/..')
    ->setConfigDirectory('config')
    ->setDotEnvPath('');
```

## Path configuration

All path-related setters accept relative paths based on the entry point script.
Usually we assume this is the `index.php` located in a `public` diectory.
The setter methods will change them to relative paths and check the validity.
This will also work with absolute paths.

#### setAppRootPath()

This is not necessary but it might make things more convenient.
Once it is set, the other path related methods will concatenate to the root-path string.

So insstead of setting the config-path relatively:
```PHP
$paramObject = (new Miniature\Component\InitParameters())
    ->setAppRootPath(__DIR__ . '/..')
    ->setConfigDirectory(__DIR__ . '/../config');
```
It may be done directly like this:
```PHP
$paramObject = (new Miniature\Component\InitParameters())
    ->setAppRootPath(__DIR__ . '/..')
    ->setConfigDirectory('config');
```


#### setConfigDirectory()

Self speaking this is the directory where all the mappings reside.
Be aware of the [reading behaviour](#reading-behaviour)
and the [environment-based orverrides](#environment-based-overrides).

```PHP
$paramObject->setConfigDirectory(__DIR__ . '/../config');
```

#### setDotEnvPath()

Self speaking this is the path where the **.env** file resides.
Learn more about the resulting behaviour
[here](#reading-the-dot-env-file).

```PHP
$paramObject->setDotEnvPath(__DIR__ . '/..');
```


## Environment settings
#### setEnv()

Set the environment name directly with a string parameter.
Userful in cases there is no **`.env`** file.
Be aware that in doubt this will lead to an override of the vanue read in the
[**.env** file](#reading-the-dot-env-file).
This might be useful for developement situations in which you want to simulate production behaviour.

#### setAvailableEnv()
This should be a list of environment names that are known in your system.
This list particulary is relevant in the [directory reading behaviour](#reading-behaviour)

It accepts an array of environment names ...
```PHP
$paramObject->setAvailableEnv(
    ['dev', 'test', 'prod', 'another']
);
```
... and a parameter list of strings as well.
```PHP
$paramObject->setAvailableEnv('dev', 'test', 'prod', 'another');
```

#### setEnvAllowingPublicAccess()
As in [setAvailableEnv()](#setavailableenv) this is a list of environment names.
It can be passed as an array or a varying number of string parameters.
```PHP
$paramObject->setEnvAllowingPublicAccess('dev', 'test', 'another');
```
Learn [here](#key-public) about the what they are for.





## Behaviour injection


#### setYamlParserDecorator()

You can provide a PHP-base YAML parser by the means of
implementing the `Miniature\Component\Reader\YamlParserDecoratorInterface`.
Learn more in the section about [YAML-support](#yaml-support).

```PHP
$paramObject->setYamlParserDecorator(new MyOwnAPP\YamlParserDecorator());
```

#### setDiSyntaxMapper()

[overriding the syntax](https://github.com/guidoerfen/miniature-di_container#syntax-overrides)

```PHP
$paramObject->setDiSyntaxMapper(new Miniature\DiContainer\Syntax\MapperSymfonyStyle());
```




## Setting available environments
By default the Component class knows three environments `dev, `prod`, `test`.
You can change that completely to your needs.
You can have as many as you wand and you can name them as you want.
```PHP
$paramObject = (new Miniature\Component\InitParameters())
    ->setAvailableEnv('develop', 'testing', 'production', 'another');
```

### Reading the dot-env file

The **.env** had become a standard for configuring valued that will be written the superglobals.
Miniature will not write to the globals and will not read from the globals.
But it will read the **.env** if you want.
Currently the only purpose for this is the value `APP_ENV` which will be transmitted to
the **`$env`** flag of your component class, representing the
[current environment](#current-environment).

Be aware that the call of [setEnv()](#setenv) will override this value.


### Current environment

The current environment is named by a string naming the envirionment the local machine is providing.
Usually it is somethjing like `developement`, `production`, or  `test`.

The value might com from the [**`.env`**-file](#reading-the-dot-env-file)
or it might be [set directly](#setenv).
Anyway, it has certain effects, most notably in the [reading behaviour](#reading-behaviour)
during the scanning of the [configuration directory](#setconfigdirectory).
