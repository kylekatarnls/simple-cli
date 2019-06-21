<?php

declare(strict_types=1);

namespace SimpleCli;

use InvalidArgumentException;
use SimpleCli\Options\Help;
use SimpleCli\Traits\Arguments;
use SimpleCli\Traits\Command as CommandTrait;
use SimpleCli\Traits\Commands;
use SimpleCli\Traits\Composer;
use SimpleCli\Traits\Documentation;
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
        Commands,
        CommandTrait,
        Parameters,
        Arguments,
        Options,
        Composer,
        Documentation;

    public function __construct(array $colors = null, array $backgrounds = null)
    {
        $this->setColors($colors, $backgrounds);
        $this->recordAutocomplete();
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

    private function parseParameters()
    {
        $this->options = [];
        $this->arguments = [];
        $this->restArguments = [];
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

        if ($optionDefinition) {
            $this->enableBooleanOption($optionDefinition, $parameter);
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

        /** @var Command & Help $commander */
        $commander = new $commandClass();

        try {
            $this->extractExpectations($commander);
            $this->parseParameters();
        } catch (InvalidArgumentException $exception) {
            $this->write($exception->getMessage(), 'red');

            return false;
        }

        $properties = array_merge($this->arguments, $this->options);

        if ($this->expectedRestArgument) {
            $properties[$this->expectedRestArgument['property']] = $this->restArguments;
        }

        foreach ($properties as $property => $value) {
            $commander->$property = $value;
        }

        if (($commander->help ?? false) && method_exists($commander, 'displayHelp')) {
            $commander->displayHelp($this);

            return true;
        }

        array_unshift($parameters, $this);

        return $commander->run(...$parameters);
    }
}
