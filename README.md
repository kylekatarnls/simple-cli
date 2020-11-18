# simple-cli

[![Latest stable version](https://img.shields.io/packagist/v/simple-cli/simple-cli.svg?style=flat-square)](https://packagist.org/packages/simple-cli/simple-cli)
[![Build status](https://img.shields.io/travis/kylekatarnls/simple-cli/master.svg?style=flat-square)](https://travis-ci.org/kylekatarnls/simple-cli)
[![StyleCI](https://styleci.io/repos/192176915/shield?style=flat-square)](https://styleci.io/repos/192176915)
[![codecov.io](https://img.shields.io/codecov/c/github/kylekatarnls/simple-cli.svg?style=flat-square)](https://codecov.io/github/kylekatarnls/simple-cli?branch=master)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat-square)](https://github.com/simple-cli/simple-cli)
[![Codacy grade](https://img.shields.io/codacy/grade/0a129ff74aca479ba21a16d8a316de07?style=flat-square)](https://app.codacy.com/project/kylekatarnls/simple-cli/dashboard)

A simple CLI framework oriented object and dependencies-free.

![Example](https://raw.githubusercontent.com/kylekatarnls/simple-cli/master/doc/img/example.jpg)

Features:

  - Auto-documentation. `--help` is auto-generated using available commands, arguments and options.
  - Detection of probable mistype and auto-suggestion.
  - Based on documentation annotations to preserve ultra-clean code.
  - Supports colors.
  - Supports interactive commands and auto-completion for CLI input.
  - Provides predefined commands: `usage` and `version`.
  - Provides predefined options: `--help`, `--quiet` and `--verbose`.
  - Provides a CLI to create programs and commands bootstraps.

## Create a command line program

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
vendor/bin/simple-cli create "MyVendorName\CliApp\EasyCalc"
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

Now let's add some argument, so your command would actually do something.

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

If `@var` is not provided, property type hint (as available since PHP 7.4)
will be used instead, or else the type will be inferred from the default
value. So `public float $number2;` will be considered as `float` and
`public $number2 = false;` will be considered as `bool`.

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

You can also allow unlimited number of arguments using the annotation `@rest`
The *rest arguments* variable will be an array with all other arguments.

So if you have 2 `@argument` and a `@rest` then if your user call your command
with 5 arguments, the first one goes to the first `@argument`, the second one
go to the second `@argument`, and the 3 other ones go as an array to the `@rest`
argument.

Of course, you can also use `@rest` with any other argument so for our `add`
command, it could be:

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
     * @rest
     *
     * The numbers to sum
     *
     * @var float[]
     */
    public $numbers = [];

    public function run(SimpleCli $cli): bool
    {
        $cli->write(array_sum($this->numbers));

        return true;
    }
}
```

Now you can call with any number of arguments:

```shell
bin/easy-calc build 2 3 1.5
```

Outputs: `6.5`

## Add options

simple-cli provides 3 standard options. The `--help -h` you already know
as `SimpleCli\Options\Help` trait you can simply `use` in your commands.

But also `--quiet -q` as `SimpleCli\Options\Quiet` that allow your user
to mute the output. If you use this trait in your command and if user
pass the option `--quiet` or `-q` methods `$cli->write()` and
`$cli->writeLine()` (and all output methods) will no longer output anything.

You can also use `--verbose -v` using `SimpleCli\Options\Verbose`:
```php
<?php

namespace MyVendorName\CliApp\Command;

use SimpleCli\Command;
use SimpleCli\Options\Verbose;
use SimpleCli\SimpleCli;

/**
 * Sum arguments.
 */
class Add implements Command
{
    use Verbose;

    public function run(SimpleCli $cli): bool
    {
        // ...

        if ($this->verbose) {
            $cli->writeLine('Log some additional info', 'light_cyan');
        }

        // ...
    }
}
```

And you can create your own option using the `@option` annotation:
```php
<?php

namespace MyVendorName\CliApp\Command;

use SimpleCli\Command;
use SimpleCli\SimpleCli;

/**
 * Sum arguments.
 */
class Add implements Command
{
    /**
     * @option
     *
     * Something the command can use.
     */
    public $foo = 'default';

    /**
     * @option show-foo
     *
     * Whether foo should be displayed or not.
     */
    public $showFoo = false;

    public function run(SimpleCli $cli): bool
    {
        if ($this->showFoo) {
            $cli->write($this->foo, 'red');
        }

        return true;
    }
}
```

```shell
bin/easy-calc --show-foo --foo=bar
```

Outputs: `bar` (in red).

Note than you can pass the name for the option and alias in the annotation:
`@option some-name, other-name, s, o` this mean `--some-name`, `--other-name`
`-s` and `-o` will all store the value in the same option variable.

Also note than if options are boolean type (`@var bool` or a boolean
default value) and have aliases, they can be merged.
If you have `@option show-foo, s` and `@option verbose, v` and pass `-vs` in
the CLI, both options will be `true`.

For non boolean options values can be set using `--foo bar` or `--foo=bar`,
both are valid. And options can come anywhere (before, after or between
arguments).

Finally, if you don't set a name, using the `@option` annotation alone
the option will have the same name as its variable and will have its
first letter as alias if it's available.

## Short annotations

Annotations for `@option`, `@argument` and `@rest` can be written on
one line.

```php
class Add implements Command
{
    /** @argument / First argument. */
    public $first = 'main';

    /** @rest / Other arguments. */
    public $others = [];

    /** @option / Something the command can use. */
    public $foo = 'default';

    /** @option show-foo, f / Whether foo should be displayed or not. */
    public $showFoo = false;

    // run(...)
}
```

With this syntax, `@var` typing is not possible, so the type will be
automatically set with the property type hint or inferring from the
default value.

## Progress bar widget

![Usage](https://raw.githubusercontent.com/kylekatarnls/simple-cli/master/doc/img/progress-bar.jpg)

```php
use SimpleCli\Command;
use SimpleCli\SimpleCli;
use SimpleCli\Widget\ProgressBar;

class SomeCommand implements Command
{
    public function run(SimpleCli $cli): bool
    {
        $bar = new ProgressBar($cli);
        $bar->start();
        $bar->setValue(0.3);
        $bar->setValue(0.7);
        $bar->setValue(1);
        $bar->end();

        return true;
    }
}
```
This will show 30% and a bar 30% full, then replace the line
with a 70% bar, and finally a full bar. It would looks like:
```txt
|  70% [===================================>               ]
```
`ProgressBar` as its settings and characters used exposed as public
properties, so you can simply set as you wish to customize the bar
style:
[src/SimpleCli/Widget/ProgressBar.php](https://github.com/kylekatarnls/simple-cli/blob/master/src/SimpleCli/Widget/ProgressBar.php)

Let's see a concrete example:
```php
public function run(SimpleCli $cli): bool
{
    $bar = new ProgressBar($cli);
    // Assuming we have a 214MB file being downloaded in a parallel process
    $bar->total = 214 * 1024 * 1024;
    // Let's customize a bit the style:
    $bar->width = 20; // inner bar size
    $dash = str_repeat('─', $bar->width);
    // Let's draw a swaure around the bar
    $bar->start = "       ┌{$dash}┐\n";
    $bar->barStart = '│';
    $bar->barEnd = '│';
    $bar->after = "\n       └{$dash}┘";
    $bar->cursor = ''; // remove bar middle cursor
    // Colorize some characters for bar (left) and empty bar (right)
    $bar->bar = $cli->colorize('█', 'cyan');
    $bar->emptyBar = $cli->colorize('░', 'light_gray');
    // as ->after contains a new line, we have
    // to rewind 1 more line
    $bar->rewind = "\033[1A\r";
    $bar->start();

    while ($bar->isInProgress()) { // while value < total
        $bar->setValue(filesize('partially-downloaded-file.part'));
        usleep(250000); // Let's refresh every 250ms
    }

    $bar->end();

    return true;
}
```

## Table widget

```php
use SimpleCli\Command;
use SimpleCli\SimpleCli;
use SimpleCli\Widget\Table;
use SimpleCli\Widget\Cell;

class SomeCommand implements Command
{
    public function run(SimpleCli $cli): bool
    {
        $data = [
            [
                new Cell('Artist', Cell::ALIGN_CENTER),
                new Cell('Song', Cell::ALIGN_CENTER),
                new Cell('Year', Cell::ALIGN_CENTER),
            ],
            [$cli->colorize('Nina Simone', 'cyan'), 'Feeling Good', 1965],
            ['The Marvelettes', 'Please Mr. Postman', 1961],
        ];
        $table = new Table($data);

        $cli->writeLine($table->format());

        return true;
    }
}
```

![Usage](https://raw.githubusercontent.com/kylekatarnls/simple-cli/master/doc/img/table.jpg)

`Table` just take data and wrap them formatter as a text-table so
it can basically be used outside `SimpleCli` context.

It provides an insanely easy way to customize the template. You
just have to give it an example using `1`, `2`, `3` and `4` as
content for a 2x2 table and it will apply your example to any
size of tables.

```php
$table = new Table([
    ['One', 'Two', 'Three'],
    ['*'],
    ['Hello', 'World', '!'],
]);
$table->template =
'╔═══╤═══╗
║ 1 │ 2 ║
╟───┼───╢
║ 3 │ 4 ║
╚═══╧═══╝';
```

Output:
```txt
╔═══════╤═══════╤═══════╗
║ One   │ Two   │ Three ║
╟───────┼───────┼───────╢
║ *     │       │       ║
╟───────┼───────┼───────╢
║ Hello │ World │ !     ║
╚═══════╧═══════╧═══════╝
```

To prevent ugly indent in your code, you can use `!template!` to start the
template at a given position:
```php
$table->template = '
    !template!
    ╔═══╤═══╗
    ║ 1 │ 2 ║
    ╟───┼───╢
    ║ 3 │ 4 ║
    ╚═══╧═══╝';
```
equivalent to the template above as indent before `!` is ignored.

You can also set the string to use as filler:
```php
$table->fill = '_-';
```

And last you can set the default alignment for each column:
```php
$table->align = [Cell::ALIGN_LEFT, Cell::ALIGN_CENTER, Cell::ALIGN_RIGHT];
```
Still you can override the alignment for a given cell using:
```php
new Cell('Text', Cell::ALIGN_CENTER)
```

`Cell` also allow you to span columns:
```php
// 2 columns-wide
(new Cell('Text'))->cols(2)

// 3 columns-wide centered
(new Cell('Text', Cell::ALIGN_CENTER))->cols(3)
```

Space taken by the text is then shared evenly among the columns.

Last, you can add color with `$cli->colorize()` to the template or
the cell contents. Color special characters are taken into account
so the display will remain properly aligned no matter if there is
some color here and there.

## API reference

In the examples above, you could see your command `run(SimpleCli $cli)`
method get a SimpleCli instance. `$cli` is your program object, an
instance of the class that extends `SimpleCli` so in the example above,
it's an instance of `EasyCalc` it means you can access from `$cli` all
methods you define in your sub-class (or the ones override to customize
your program) and all methods available from the `SimpleCli` inherited class:

<i start-api-reference></i>

### getVersionDetails(): string

> Get details to be displayed with the version command.

### getVersion(): string

> Get the composer version of the package handling the CLI program.

### displayVariable(int $length, string $variable, array $definition, $defaultValue): void

> Output standard command variable (argument or option).

### autocomplete(string $start): array

> Get possible completions for a given start.

### read($prompt, $completion): string

> Ask the user $prompt and return the CLI input.

### isMuted(): bool

> Returns true if the CLI program is muted (quiet).

### setMuted(bool $muted): void

> Set the mute state.

### mute(): void

> Mute the program (no more output).

### unmute(): void

> Unmute the program (enable output).

### enableColors(): void

> Enable colors support in command line.

### disableColors(): void

> Disable colors support in command line.

### setEscapeCharacter(string $escapeCharacter): void

> Set a custom string for escape command in CLI strings.

### setColors(array $colors, array $backgrounds): void

> Set colors palette.

### colorize(string $text, string $color, string $background): string

> Return $text with given color and background color.

### rewind(int $length): void

> Rewind CLI cursor $length characters behind, if $length is omitted, use the last written string length.

### write(string $text, string $color, string $background): void

> Output $text with given color and background color.

### writeLine(string $text, string $color, string $background): void

> Output $text with given color and background color and add a new line.

### rewrite(string $text, string $color, string $background): void

> Replace last written line with $text with given color and background color.

### rewriteLine(string $text, string $color, string $background): void

> Replace last written line with $text with given color and background color and re-add the new line.

### getName(): string

> Get the name of the CLI program.

### getFile(): string

> Get the current program file called from the CLI.

### getCommands(): array

> Get the list of commands expect those provided by SimpleCli.

### getAvailableCommands(): array

> Get the list of commands included those provided by SimpleCli.

### getCommand(): string

> Get the selected command.

### getParameters(): array

> Get raw parameters (options and arguments) not filtered.

### getParameterValue(string $parameter, array $parameterDefinition): string|int|float|bool|null

> Cast argument/option according to type in the definition.

### getArguments(): array

> Get list of current filtered arguments.

### getExpectedArguments(): array

> Get definitions of expected arguments.

### getRestArguments(): array

> Get the rest of filtered arguments.

### getExpectedRestArgument(): array

> Get definition for the rest argument if a @rest property given.

### getOptions(): array

> Get list of current filtered options.

### getExpectedOptions(): array

> Get definition of expected options.

### getOptionDefinition(string $name): array

> Get option definition and expected types/values of a given one identified by name or alias.

### getPackageName(): string

> Get the composer package name that handle the CLI program.

### setVendorDirectory(string $vendorDirectory): void

> Set the vendor that should contains packages including composer/installed.json.

### getVendorDirectory(): string

> Get the vendor that should contains packages including composer/installed.json.

### getInstalledPackages(): array<string|int, array<string, string>>

> Get the list of packages installed with composer.

### getInstalledPackage(string $name): SimpleCli\Composer\InstalledPackage

> Get data for a given installed package.

### getInstalledPackageVersion(string $name): string

> Get the version of a given installed package.

### extractClassNameDescription(string $className): string

> Get PHP comment doc block content of a given class.

### extractAnnotation(string $source, string $annotation): string

> Extract an annotation content from a PHP comment doc block.

<i end-api-reference></i>
