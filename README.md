# simple-cli

[![Latest Stable Version](https://img.shields.io/packagist/v/simple-cli/simple-cli.svg?style=flat-square)](https://packagist.org/packages/simple-cli/simple-cli)
[![Build Status](https://img.shields.io/travis/kylekatarnls/simple-cli/master.svg?style=flat-square)](https://travis-ci.org/kylekatarnls/simple-cli)
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

(You may need to run `composer update` or `composer dump-autoload` to get the autoload in effect.)

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

It will create `bin/easy-calc` for unix systems and `bin/easy-calc.bat` for Windows OS.

You can add it to **composer.json** so users can call it via composer:

```json
"bin": [
    "bin/easy-calc"
],
```

Let's test your CLI program now:

```shell
bin/easy-calc
```

![Usage](https://raw.githubusercontent.com/kylekatarnls/simple-cli/master/doc/img/usage.jpg)

As you can see, by default, simple-cli provide 2 commands: `list` (that is also the default when the user did not
choose a command) that list the commands available and `version` (that will show the version of your composer package
and version details you may add if you publish it).

Note that if you don't want to publish it, you can either customize what version should display:

```php
class EasyCalc extends SimpleCli
{
    public function getCommands() : array
    {
        return [];
    }

    public function getVersion(): string
    {
        return '1.0.0';
    }
}
```

Or you can disable any of the default commands:

```php
class EasyCalc extends SimpleCli
{
    public function getCommands() : array
    {
        return [
            'version' => false,
        ];
    }
}
```

## Add commands

Now it's time for your CLI to get actual commands. To create an `add`
command for example, you can create a `MyVendorName\CliApp\Command\Add` class:

```php
<?php

namespace MyVendorName\CliApp\Command;

use SimpleCli\Command;
use SimpleCli\Options\Help;
use SimpleCli\SimpleCli;

/**
 * Sum arguments.
 */
class Add implements Command
{
    use Help;

    public function run(SimpleCli $cli): bool
    {
    }
}
```

Then add this command with a name in your CLI:

```php
class EasyCalc extends SimpleCli
{
    public function getCommands() : array
    {
        return [
            'add' => \MyVendorName\CliApp\Command\Add::class,
        ];
    }
}
```

If you run `bin/easy-calc` (or `bin/easy-calc list`) again, you will now
see `add` as an available command. And the comment you put in `/** */`
appears in the description. 

If you run `bin/easy-calc add --help` (or `bin/easy-calc add -h`) you will
the documentation of your command based on the options and arguments defined.
As you see, there is only `--help` option (or `-h` alias) provided by the
trait `SimpleCli\Options\Help` (we highly recommend to always use this trait
in your commands).

## Add arguments

Now let's add some argument so your command would actually do something.

```php
<?php

namespace MyVendorName\CliApp\Command;

use SimpleCli\Command;
use SimpleCli\Options\Help;
use SimpleCli\SimpleCli;

/**
 * Sum arguments.
 */
class Add implements Command
{
    use Help;

    /**
     * @argument
     *
     * The first number
     *
     * @var float
     */
    public $number1 = 0;

    /**
     * @argument
     *
     * The second number
     *
     * @var float
     */
    public $number2 = 0;

    public function run(SimpleCli $cli): bool
    {
        $cli->write($this->number1 + $this->number2);

        return true;
    }
}
```

The `@argument` annotation allows simple-cli to know it's an argument.

If you run `bin/easy-calc add --help` you will see they appear in the
help with their description, type and default value.

Now's time to execute your command:

```shell
bin/easy-calc add 2 3
```

It outputs `5` :rocket:

Note than `run()` must return a boolean:
 - `true` for successful command (exit code 0)
 - `false` for error (exit code 1)
