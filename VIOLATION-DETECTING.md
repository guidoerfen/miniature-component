# Detecting Violations

Miniature\Component offers a violation detecting mechanism for your pre-deployment-pipeline:

```PHP
Core\LocalAppComponent::getInstance()
    ->detectViolations(__DIR__ . '/../');
```

The directory will be scanned recursively.  
The example below demonstrates what the detector is looking for:
- instantiations per `new`
- static instance-access methods
- fully classifies and simple class names
- use statements with and without alias
  - these only will be complained in connection to an access- or con

It is also possible to skip the detection of certain wirings by adding the
[**`skip violation detection`**](https://github.com/guidoerfen/miniature-component/blob/master/README.md#key-skipviolationscan)-flag
to the DI-mapping.

```shell
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                                                   *
*                              ,--.      ,--.        ,--.                                           *
*                     ,--,--,--`--,--,--,`--',--,--,-'  '-,--.,--,--.--.,---.                       *
*                     |        ,--|      ,--' ,-.  '-.  .-|  ||  |  .--| .-. :                      *
*                     |  |  |  |  |  ||  |  \ '-'  | |  | '  ''  |  |  \   --.                      *
*                     `--`--`--`--`--''--`--'`--`--' `--'  `----'`--'   `----'                      *
*                     M A P P I N G  -  V I O L A T I O N S    D E T E C T O R                      *
*                                                                                                   *
*                                                                                                   *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                                                   *
*                                Detecting:                                                         *
*                                - Violations concerning Component:                                 *
*                                  Core\LocalAppComponent                                           *
*                                - Root-Directory to be scanned:                                    *
*                                  '/var/www/my-app/sub/folder/'                                    *
*                                                                                                   *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *



* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                         *
*              Violation found in file                                    *
*              '/var/www/my-app/sub/folder/public/index.php'              *
*                                                                         *
*              Wiring declared in file                                    *
*              '/var/www/my-app/sub/folder/config/di-mapping.php'         *
*                                                                         *
*              Class concerned:                                           *
*              'AppDemo\SingletonOne'                                     *
*                                                                         *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
    1.    AppDemo\SingletonOne::getInstance()




* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                         *
*              Violation found in file                                    *
*              '/var/www/my-app/sub/folder/public/violations.php'         *
*                                                                         *
*              Wiring declared in file                                    *
*              '/var/www/my-app/sub/folder/config/di-mapping.php'         *
*                                                                         *
*              Class concerned:                                           *
*              'AppDemo\PersonEnhancer'                                   *
*                                                                         *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
    1.    new AppDemo\PersonEnhancer()





* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                         *
*            Violation found in file                                      *
*            '/var/www/my-app/sub/folder/src/AppDemo/Yammi.php'           *
*                                                                         *
*            Wiring declared in file                                      *
*            '/var/www/my-app/sub/folder/config/di-mapping-doc.php'       *
*                                                                         *
*            Class concerned:                                             *
*            'AppDemo\PersonManager'                                      *
*                                                                         *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

    Found 'use'-statement: use AppDemo\PersonManager as Manager;

    1.    new Manager([])




* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                         *
*              Violation found in file                                    *
*              '/var/www/my-app/sub/folder/src/AppDemo/Yammi.php'         *
*                                                                         *
*              Wiring declared in file                                    *
*              '/var/www/my-app/sub/folder/config/di-mapping.yaml'        *
*                                                                         *
*              Class concerned:                                           *
*              'AppDemo\Yimmi'                                            *
*                                                                         *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

    Found 'use'-statement: use AppDemo\Yimmi as Jimi;

    1.    new Jimi(
                [
    
    
                ])


    2.    new Jimi([])




* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                         *
*              Violation found in file                                    *
*              '/var/www/my-app/sub/folder/src/AppDemo/Yimmi.php'         *
*                                                                         *
*              Wiring declared in file                                    *
*              '/var/www/my-app/sub/folder/config/di-mapping.yaml'        *
*                                                                         *
*              Class concerned:                                           *
*              'AppDemo\Yammi'                                            *
*                                                                         *
*                                                                         *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

    Found 'use'-statement: use AppDemo\Yammi;

    1.    new Yammi(['offset' => 'value'])



* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                           Violations found: 6                           *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *

* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
*                                                                                                   *
*                                  E N D Detecting for Component:                                   *
*                                  - Core\LocalAppComponent                                         *
*                                                                                                   *
* * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
```


