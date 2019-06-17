# simple-cli

[![Latest Stable Version](https://img.shields.io/packagist/v/simple-cli/simple-cli.svg?style=flat-square)](https://packagist.org/packages/nesbot/carbon)
[![Build Status](https://img.shields.io/travis/kylekatarnls/simple-cli/master.svg?style=flat-square)](https://travis-ci.org/briannesbitt/simple-cli)
[![StyleCI](https://styleci.io/repos/192176915/shield?style=flat-square)](https://styleci.io/repos/192176915)
[![codecov.io](https://img.shields.io/codecov/c/github/kylekatarnls/simple-cli.svg?style=flat-square)](https://codecov.io/github/kylekatarnls/simple-cli?branch=master)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat-square)](https://github.com/simple-cli/simple-cli)

A simple CLI framework oriented object and dependencies-free.

# Create a command line program

You can add your command line program in any existing composer app, or create a new one using `composer init`.

Then add simple-cli:

```shell
composer require simple-cli/simple-cli
```

Let say your app allows to add or multiply 2 arguments and you want to call `easy-calc` in your CLI, so you need to
create an `EasyCalc` class that extends `SimpleCli\SimpleCli`.

So first check you have a PSR autoload set in your **composer.json**:
```json
"autoload": {
    "psr-4": {
        "MyVendorName\\": "src/MyVendorName/"
    }
},
```

Then create the class so it can be autoloaded, so with the example above, we can create the file
`src/MyVendorName/CliApp/EasyCalc.php`:

```php
<?php

namespace MyVendorName\CliApp;

use SimpleCli\SimpleCli;

class EasyCalc extends SimpleCli
{
    public function getCommands() : array
    {
        return []; // Your class needs to implement the getCommands(), we'll see later what to put in here.
    }
}
```

By default the name of the program will be calculated from the class name, here `EasyCalc` becomes `easy-calc` but
you can pick any name by adding `protected $name = 'my-custom-name';` in your class.

Now you can run from the console:

```shell
vendor/bin/simple-cli create MyVendorName\CliApp\EasyCalc
```

```php
#!/usr/bin/env php
<?php

$dir = __DIR__.'/..';

if (!file_exists($dir.'/autoload.php')) {
    $dir = __DIR__.'/../vendor';
}

if (!file_exists($dir.'/autoload.php')) {
    $dir = __DIR__.'/../../..';
}

if (!file_exists($dir.'/autoload.php')) {
    echo 'Autoload not found.';
    exit(1);
}

include $dir.'/autoload.php';

exit((new \Carbon\Cli\CarbonCommand())(...array_slice($argv, 1)) ? 0 : 1);
```

