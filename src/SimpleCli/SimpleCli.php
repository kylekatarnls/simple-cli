<?php

declare(strict_types=1);

namespace SimpleCli;

use InvalidArgumentException;
use SimpleCli\Options\Help;
use SimpleCli\Options\Quiet;
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

/**
 * Class SimpleCli.
 *
 * @property string                                    $command
 * @property string[]                                  $parameters
 * @property array<string, string|int|float|bool|null> $arguments
 * @property array<array<string, mixed>>               $expectedArguments
 * @property array<string|int|float|bool|null>         $restArguments
 * @property array<string, mixed>                      $options
 * @property array<array<string, mixed>>               $expectedOptions
 */
abstract class SimpleCli implements Writer
{
    use Input;
    use Output;
    use Name;
    use File;
    use Commands;
    use CommandTrait;
    use Parameters;
    use Arguments;
    use Options;
    use Composer;
    use Documentation;

    /**
     * @param array<string, string>|null $colors
     * @param array<string, string>|null $backgrounds
     */
    public function __construct(?array $colors = null, ?array $backgrounds = null)
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
    public function getVersion(): string
    {
        $packageName = $this->getPackageName();
        $start = $packageName === '' ? '' : $this->colorize($packageName, 'green').' version ';

        return $start.
            $this->colorize($this->getInstalledPackageVersion($packageName), 'brown').
            $this->getVersionDetails();
    }

    /**
     * Output standard command variable (argument or option).
     *
     * @param int                  $length       Length of the left column.
     * @param string               $variable     Argument/option name.
     * @param array<string, mixed> $definition   Definition infos. Should contain description, and either values or
     *                                           type.
     * @param mixed                $defaultValue Default value.
     */
    public function displayVariable(int $length, string $variable, array $definition, $defaultValue): void
    {
        $this->write('  ');
        $this->write($variable, 'green');
        $this->write(str_repeat(' ', $length - strlen($variable)));
        $this->writeLine(
            str_replace(
                "\n",
                "\n".str_repeat(' ', $length + 2),
                $definition['description']."\n".
                $this->colorize(str_pad($definition['values'] ?: $definition['type'], 16, ' ', STR_PAD_RIGHT), 'cyan').
                $this->colorize('default: '.$this->getValueExport($defaultValue), 'brown')
            )
        );
    }

    /**
     * Execute the command.
     *
     * @param string $file
     * @param string $command
     * @param mixed  ...$parameters
     *
     * @return bool
     */
    public function __invoke(string $file, string $command = 'list', ...$parameters): bool
    {
        $commands = $this->getAvailableCommands();
        $this->file = $file;
        $command = $this->getCommandName($commands, $command);

        if (!$command) {
            return false;
        }

        $this->command = $command;
        $this->parameters = $parameters;

        $commandClass = $this->getCommandClassFromName($commands, $command);

        if (!$commandClass) {
            return false;
        }

        $commander = $this->createCommander($commandClass);

        if (!$commander) {
            return false;
        }

        if ($this->hasTraitFeatureEnabled(/* @var Quiet $commander */ $commander, Quiet::class, 'quiet')) {
            $this->mute();
        }

        /**
         * @var Help $helper
         * @psalm-suppress UndefinedDocblockClass
         */
        $helper = $commander;

        if ($this->hasTraitFeatureEnabled($commander, Help::class, 'help')) {
            /** @psalm-suppress UndefinedDocblockClass */
            $helper->displayHelp($this); // @phan-suppress-current-line PhanUndeclaredMethod

            return true;
        }

        array_unshift($parameters, $this);

        return $commander->run(...$parameters);
    }

    /**
     * Return an array of traits directly in use by the given class.
     *
     * @param Command|string $commander
     *
     * @return string[]
     */
    protected function getCommandTraits($commander): array
    {
        return class_uses($commander) ?: [];
    }

    /**
     * Determines if a Command instance has a given feature (detected by a trait or a property).
     *
     * @param Command|null $commander
     * @param string       $trait
     * @param string       $property
     *
     * @return bool
     */
    protected function hasTraitFeatureEnabled(
        Command $commander = null,
        string $trait = '',
        string $property = ''
    ): bool {
        $traits = $commander ? array_merge(
            $this->getCommandTraits($commander),
            ...array_map([$this, 'getCommandTraits'], array_values(class_parents($commander) ?: []))
        ) : [];

        return isset($traits[$trait]) && $commander && ($commander->$property ?? false);
    }

    /**
     * Get value export for a given value.
     *
     * @param mixed $value
     *
     * @return string
     */
    protected function getValueExport($value): string
    {
        $value = (string) var_export($value, true);
        $value = (string) preg_replace('/^\s*array\s*\(([\s\S]*)\)\s*$/', '[$1]', $value);
        $value = (string) preg_replace('/^\s*\[\s+]$/', '[]', $value);

        return strtr(
            $value,
            [
                'NULL'  => 'null',
                'FALSE' => 'false',
                'TRUE'  => 'true',
            ]
        );
    }

    private function parseParameters(): void
    {
        $this->options = [];
        $this->arguments = [];
        $this->restArguments = [];
        $optionDefinition = null;
        $parameter = '';

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

    /**
     * @param array<string, mixed> $commands
     * @param string               $command
     *
     * @return string|null
     */
    private function findClosestCommand(array $commands, string $command): ?string
    {
        $words = new WordsList(array_keys($commands));
        $closestCommand = $words->findClosestWord($command);

        if ($closestCommand) {
            $this->writeLine();

            do {
                $this->write('Do you mean ');
                $this->write($closestCommand, 'light_blue');
                $this->write('?');

                $answer = strtolower(substr($this->read(' [y/n]: '), 0, 1));
            } while ($answer !== 'n' && $answer !== 'y');

            if ($answer === 'y') {
                $this->writeLine();

                return $closestCommand;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $commands
     * @param string               $command
     *
     * @return string|null
     */
    private function getCommandName(array $commands, string $command): ?string
    {
        if (!isset($commands[$command])) {
            $this->write("Command $command not found", 'red');

            return $this->findClosestCommand($commands, $command);
        }

        return $command;
    }

    /**
     * @param array<string, mixed> $commands
     * @param string               $command
     *
     * @psalm-return class-string|null
     *
     * @return string|null
     */
    private function getCommandClassFromName(array $commands, string $command): ?string
    {
        /**
         * @var string $commandClass
         */
        $commandClass = $commands[$command];

        if (!is_subclass_of($commandClass, Command::class)) {
            $this->write("$commandClass needs to implement ".Command::class, 'red');

            return null;
        }

        return $commandClass;
    }

    /**
     * @param string $commandClass
     * @psalm-param class-string $commandClass
     *
     * @return Command|null
     */
    private function createCommander(string $commandClass): ?Command
    {
        /**
         * @var Command $commander
         * @psalm-var Command $commander
         */
        $commander = new $commandClass();

        try {
            $this->extractExpectations($commander);
            $this->parseParameters();
        } catch (InvalidArgumentException $exception) {
            $this->write($exception->getMessage(), 'red');

            return null;
        }

        $properties = array_merge($this->arguments, $this->options);

        if ($this->expectedRestArgument) {
            $properties[$this->expectedRestArgument['property']] = $this->restArguments;
        }

        foreach ($properties as $property => $value) {
            $commander->$property = $value;
        }

        return $commander;
    }
}
