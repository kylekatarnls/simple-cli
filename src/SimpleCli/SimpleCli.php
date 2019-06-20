<?php
declare(strict_types=1);

namespace SimpleCli;

use InvalidArgumentException;
use ReflectionClass;
use ReflectionObject;
use SimpleCli\Command\Usage;
use SimpleCli\Command\Version;
use SimpleCli\Composer\InstalledPackage;
use SimpleCli\Traits\Arguments;
use SimpleCli\Traits\Command as CommandTrait;
use SimpleCli\Traits\File;
use SimpleCli\Traits\Input;
use SimpleCli\Traits\Name;
use SimpleCli\Traits\Options;
use SimpleCli\Traits\Output;
use SimpleCli\Traits\Parameters;

abstract class SimpleCli
{
    use Input,
        Output,
        Name,
        File,
        CommandTrait,
        Parameters,
        Arguments,
        Options;

    public function __construct(array $colors = null, array $backgrounds = null)
    {
        $this->setColors($colors, $backgrounds);
        $this->recordAutocomplete();
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
            ? @json_decode((string) file_get_contents($installedJson), true)
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
        $doc = (string) preg_replace('/^\s*\/\*+/', '', $doc);
        $doc = (string) preg_replace('/\s*\*+\/$/', '', $doc);
        $doc = (string) preg_replace('/^\s*\*\s?/m', '', $doc);

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

        return $this->cleanPhpDocComment((string) $doc);
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

        $source = (string) preg_replace_callback('/^'.preg_quote($code).'( ([^\n]*(\n+'.str_repeat(' ', $length).'[^\n]*)*))?/m', function ($match) use (&$result, $length) {
            $result = (string) str_replace("\n".str_repeat(' ', $length), "\n", $match[2] ?? '');

            return '';
        }, $source);

        $source = trim($source, "\n");

        return $result;
    }

    private function extractExpectations(Command $command): void
    {
        $this->expectedArguments = [];
        $this->expectedOptions = [];

        $reflexion = new ReflectionObject($command);

        foreach ($reflexion->getProperties() as $property) {
            $name = $property->getName();
            $doc = $this->cleanPhpDocComment((string) $property->getDocComment());
            $argument = $this->extractAnnotation($doc, 'argument') !== null;
            $option = $this->extractAnnotation($doc, 'option');
            $values = $this->extractAnnotation($doc, 'values');
            $var = str_replace('boolean', 'bool', $this->extractAnnotation($doc, 'var') ?: 'string');

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
