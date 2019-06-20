<?php

namespace SimpleCli;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionObject;
use SimpleCli\Command\Usage;
use SimpleCli\Command\Version;
use SimpleCli\Composer\InstalledPackage;

abstract class SimpleCli
{
    /**
     * @var string|null
     */
    protected $name = null;

    /**
     * @var string
     */
    protected $file;

    /**
     * @var string
     */
    protected $command;

    /**
     * @var string[]
     */
    protected $parameters;

    /**
     * @var array
     */
    protected $expectedArguments;

    /**
     * @var array
     */
    protected $expectedOptions;

    /**
     * @var array
     */
    protected $arguments;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var array
     */
    protected $colors = [
        'black'        => '0;30',
        'dark_gray'    => '1;30',
        'blue'         => '0;34',
        'light_blue'   => '1;34',
        'green'        => '0;32',
        'light_green'  => '1;32',
        'cyan'         => '0;36',
        'light_cyan'   => '1;36',
        'red'          => '0;31',
        'light_red'    => '1;31',
        'purple'       => '0;35',
        'light_purple' => '1;35',
        'brown'        => '0;33',
        'yellow'       => '1;33',
        'light_gray'   => '0;37',
        'white'        => '1;37',
    ];

    /**
     * @var array
     */
    protected $backgrounds = [
        'black'      => '40',
        'red'        => '41',
        'green'      => '42',
        'yellow'     => '43',
        'blue'       => '44',
        'magenta'    => '45',
        'cyan'       => '46',
        'light_gray' => '47',
    ];

    /**
     * @var string
     */
    protected $lastText = '';

    /**
     * @var string
     */
    protected $escapeCharacter = "\033";

    /**
     * @var \Closure|callable|array|null
     */
    protected $currentCompletion = null;

    public function __construct(array $colors = null, array $backgrounds = null)
    {
        if ($colors) {
            $this->colors = $colors;
        }

        if ($backgrounds) {
            $this->backgrounds = $backgrounds;
        }

        if (extension_loaded('readline') && function_exists('readline_completion_function')) {
            readline_completion_function([$this, 'autocomplete']);
        }
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the current program file called from the CLI.
     *
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * Get the selected command.
     *
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    /**
     * Get raw parameters (options and arguments) not filtered.
     *
     * @return string[]
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    /**
     * Get list of current filtered options.
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Get list of current filtered arguments.
     *
     * @return array
     */
    public function getArguments(): array
    {
        return $this->arguments;
    }

    /**
     * Get the list of commands expect those provided by SimpleCli.
     *
     * @return array
     */
    public function getCommands(): array
    {
        return [];
    }

    /**
     * Get the composer package name that handle the CLI program.
     *
     * @return string
     */
    public function getPackageName(): string
    {
        return '';
    }

    /**
     * Get details to be displayed with the version command.
     *
     * @return string
     */
    public function getVersionDetails(): string
    {
        return '';
    }

    /**
     * Get the composer version of the package handling the CLI program.
     *
     * @return string
     */
    public function getVersion()
    {
        $packageName = $this->getPackageName();
        $start = $packageName === '' ? '' : $this->colorize($packageName, 'green').' version ';

        return $start.$this->colorize($this->getInstalledPackageVersion($packageName), 'brown').$this->getVersionDetails();
    }

    /**
     * Get the list of packages installed with composer.
     *
     * @return array
     */
    public function getInstalledPackages()
    {
        $installedJson = __DIR__.'/../../../../composer/installed.json';
        $installedData = file_exists($installedJson)
            ? @json_decode(file_get_contents($installedJson) ?: '', true)
            : null;

        return $installedData ?: [];
    }

    /**
     * Get data for a given installed package.
     *
     * @param string $name
     *
     * @return InstalledPackage|null
     */
    public function getInstalledPackage(string $name): ?InstalledPackage
    {
        foreach ($this->getInstalledPackages() as $package) {
            if (($package['name'] ?? null) === $name) {
                return new InstalledPackage($package);
            }
        }

        return null;
    }

    /**
     * Get the version of a given installed package.
     *
     * @param string $name
     *
     * @return string
     */
    public function getInstalledPackageVersion(string $name): string
    {
        $package = $this->getInstalledPackage($name);
        $version = $package ? $package->version : null;

        return $version ?: 'unknown';
    }

    /**
     * Set a custom string for escape command in CLI strings.
     *
     * @param string $escapeCharacter
     */
    public function setEscapeCharacter(string $escapeCharacter): void
    {
        $this->escapeCharacter = $escapeCharacter;
    }

    /**
     * Get possible completions for a given start.
     *
     * @param string $start
     *
     * @return string[]
     */
    public function autocomplete(string $start = ''): array
    {
        if (is_array($this->currentCompletion)) {
            $length = strlen($start);

            return array_filter($this->currentCompletion, function ($suggestion) use ($length, $start) {
                return substr($suggestion, 0, $length) === $start;
            });
        }

        return $this->currentCompletion ? ($this->currentCompletion)($start) : [];
    }

    protected function getColorCode(string $color, array $colors = null): string
    {
        $colors = $colors ?: $this->colors;
        $color = $colors[$color] ?? $color;

        return $this->escapeCharacter.'['.$color.'m';
    }

    /**
     * Return $text with given color and background color.
     *
     * @param string      $text
     * @param string|null $color
     * @param string|null $background
     *
     * @return string
     */
    public function colorize(string $text = '', string $color = null, string $background = null): string
    {
        if (!$color && !$background) {
            return $text;
        }

        $color = $color ? $this->getColorCode($color) : '';
        $background = $background ? $this->getColorCode($background, $this->backgrounds) : '';

        return $color.$background.$text.$this->escapeCharacter.'[0m';
    }

    /**
     * Ask the user $prompt and return the CLI input.
     *
     * @param string              $prompt
     * @param array|callable|null $completion
     *
     * @return string
     */
    public function read($prompt, $completion = null): string
    {
        $this->currentCompletion = $completion;

        return readline($prompt);
    }

    /**
     * Rewind CLI cursor $length characters behind, if $length is omitted, use the last written string length.
     *
     * @param int|null $length
     */
    public function rewind(int $length = null): void
    {
        if ($length === null) {
            $length = strlen($this->lastText);
        }

        echo $this->escapeCharacter.'['.$length.'D';
    }

    /**
     * Output $text with given color and background color.
     *
     * @param string      $text
     * @param string|null $color
     * @param string|null $background
     */
    public function write(string $text = '', string $color = null, string $background = null): void
    {
        $this->lastText = $text;

        if ($color) {
            $text = $this->colorize($text, $color, $background);
        }

        echo $text;
    }

    /**
     * Output $text with given color and background color and add a new line.
     *
     * @param string      $text
     * @param string|null $color
     * @param string|null $background
     */
    public function writeLine(string $text = '', string $color = null, string $background = null): void
    {
        $this->write("$text\n", $color, $background);
    }

    /**
     * Replace last written line with $text with given color and background color.
     *
     * @param string      $text
     * @param string|null $color
     * @param string|null $background
     */
    public function rewrite(string $text = '', string $color = null, string $background = null): void
    {
        $this->rewind();
        $this->write($text, $color, $background);
    }

    /**
     * Replace last written line with $text with given color and background color and re-add the new line.
     *
     * @param string      $text
     * @param string|null $color
     * @param string|null $background
     */
    public function rewriteLine(string $text = '', string $color = null, string $background = null): void
    {
        $this->write("\r$text", $color, $background);
    }

    /**
     * Get the list of commands included those provided by SimpleCli.
     *
     * @return array
     */
    public function getAvailableCommands(): array
    {
        return array_filter(array_merge([
            'list'    => Usage::class,
            'version' => Version::class,
        ], $this->getCommands()), 'boolval');
    }

    private function cleanPhpDocComment(string $doc): string
    {
        $doc = preg_replace('/^\s*\/\*+/', '', $doc) ?: '';
        $doc = preg_replace('/\s*\*+\/$/', '', $doc) ?: '';
        $doc = preg_replace('/^\s*\*\s?/m', '', $doc) ?: '';

        return rtrim($doc);
    }

    /**
     * Get PHP comment doc block content of a given class.
     *
     * @param string $className
     *
     * @return string
     */
    public function extractClassNameDescription(string $className): string
    {
        try {
            $reflexion = new ReflectionClass($className);

            $doc = $reflexion->getDocComment();
        } catch (\ReflectionException $e) {
            $doc = null;
        }

        if (empty($doc)) {
            return $className;
        }

        return $this->cleanPhpDocComment($doc ?: '');
    }

    /**
     * Extract an annotation content from a PHP comment doc block.
     *
     * @param string $source
     * @param string $annotation
     *
     * @return string|null
     */
    public function extractAnnotation(string &$source, string $annotation): ?string
    {
        $code = "@$annotation";
        $length = strlen($code) + 1;
        $result = null;

        $source = preg_replace_callback('/^'.preg_quote($code).'( ([^\n]*(\n+'.str_repeat(' ', $length).'[^\n]*)*))?/m', function ($match) use (&$result, $length) {
            $result = str_replace("\n".str_repeat(' ', $length), "\n", $match[2] ?? '');

            return '';
        }, $source);

        $source = trim($source, "\n");

        return $result;
    }

    /**
     * Get option definition and expected types/values of a given one identified by name or alias.
     *
     * @param string $name
     *
     * @return array
     */
    public function getOptionDefinition(string $name): array
    {
        foreach ($this->expectedOptions as $property => $definition) {
            if (in_array($name, $definition['names'])) {
                return $definition;
            }
        }

        $name = strlen($name) === 1 ? "-$name" : "--$name";

        throw new InvalidArgumentException(
            "Unknown $name option"
        );
    }

    private function extractExpectations(Command $command): void
    {
        $reflexion = new ReflectionObject($command);

        foreach ($reflexion->getProperties() as $property) {
            $name = $property->getName();
            $doc = $this->cleanPhpDocComment($property->getDocComment() ?: '');
            $argument = $this->extractAnnotation($doc, 'argument') !== null;
            $option = $this->extractAnnotation($doc, 'option');
            $values = $this->extractAnnotation($doc, 'values');
            $var = str_replace('boolean', 'bool', $this->extractAnnotation($doc, 'var'));

            if ($option === '') {
                $option = "$name, ".substr($name, 0, 1);
            }

            if ($option) {
                if ($argument) {
                    throw new InvalidArgumentException(
                        'A property cannot be both @option and @argument'
                    );
                }

                $this->expectedOptions[] = [
                    'property'    => $name,
                    'names'       => array_map('trim', explode(',', $option)),
                    'description' => $doc,
                    'values'      => $values,
                    'type'        => $var,
                ];

                continue;
            }

            if ($argument) {
                $this->expectedArguments[] = [
                    'property'    => $name,
                    'description' => $doc,
                    'values'      => $values,
                    'type'        => $var,
                ];
            }
        }
    }

    private function parseArgument(string $argument): void
    {
        $definition = $this->expectedArguments[count($this->arguments)] ?? null;

        if (!$definition) {
            $count = count($this->expectedArguments);

            throw new InvalidArgumentException(
                'Expect only '.$count.' argument'.($count === 1 ? '' : 's')
            );
        }

        $this->arguments[$definition['property']] = $this->getParameterValue($argument, $definition);
    }

    private function enableBooleanOption(array $definition, string $name, string $value = null)
    {
        if ($definition['type'] !== 'bool') {
            throw new InvalidArgumentException(
                "$name option is not a boolean, so you can't use it in a aliases group"
            );
        }

        if ($value) {
            throw new InvalidArgumentException(
                "$name option is boolean and should not have value"
            );
        }

        $this->options[$definition['property']] = true;
    }

    private function setOption(string $name, string $value = null, array &$optionDefinition = null)
    {
        $definition = $this->getOptionDefinition($name);
        $name = strlen($name) === 1 ? "-$name" : "--$name";

        if ($definition['type'] === 'bool') {
            $this->enableBooleanOption($definition, $name, $value);

            return;
        }

        if ($value) {
            $this->options[$definition['property']] = $this->getParameterValue($value, $definition);

            return;
        }

        $optionDefinition = $definition;
    }

    private function parseOption(string $option, array &$optionDefinition = null): void
    {
        $parts = explode('=', $option, 2);
        $name = $parts[0];
        $value = $parts[1] ?? null;

        if (substr($name, 1, 1) !== '-') {
            if (strlen($name) > 2) {
                if ($value) {
                    throw new InvalidArgumentException(
                        "Unable to parse $option, maybe you would mean -$option"
                    );
                }

                foreach (str_split(substr($name, 1)) as $alias) {
                    $this->enableBooleanOption($this->getOptionDefinition($alias), "-$alias");
                }

                return;
            }

            $this->setOption(substr($name, 1), $value, $optionDefinition);

            return;
        }

        $this->setOption(substr($name, 2), $value, $optionDefinition);
    }

    /**
     * @param string $parameter
     * @param array  $optionDefinition
     * @return string
     */
    public function getParameterValue(string $parameter, array $optionDefinition)
    {
        if (!settype($parameter, $optionDefinition['type'])) {
            throw new InvalidArgumentException(
                "Cannot cast $parameter to ".$optionDefinition['type']
            );
        }

        return $parameter;
    }

    private function parseParameters()
    {
        $this->options = [];
        $this->arguments = [];
        $optionDefinition = null;

        foreach ($this->parameters as $parameter) {
            if ($optionDefinition) {
                $this->options[$optionDefinition['property']] = $this->getParameterValue($parameter, $optionDefinition);
                $optionDefinition = null;

                continue;
            }

            substr($parameter, 0, 1) === '-'
                ? $this->parseOption($parameter, $optionDefinition)
                : $this->parseArgument($parameter);
        }
    }

    public function __invoke(string $file, string $command = 'list', ...$parameters): bool
    {
        $this->file = $file;
        $this->command = $command;
        $this->parameters = $parameters;

        $commands = $this->getAvailableCommands();

        if (!isset($commands[$command])) {
            $this->write("Command $command not found", 'red');

            return false;
        }

        $commandClass = $commands[$command];

        if (!is_subclass_of($commandClass, Command::class)) {
            $this->write("$commandClass needs to implement ".Command::class, 'red');

            return false;
        }

        /** @var Command $commander */
        $commander = new $commandClass();

        try {
            $this->extractExpectations($commander);
            $this->parseParameters();
        } catch (InvalidArgumentException $exception) {
            $this->write($exception->getMessage(), 'red');

            return false;
        }

        foreach (array_merge($this->arguments, $this->options) as $property => $value) {
            $commander->$property = $value;
        }

        return $commander->run($this, ...$parameters);
    }
}
