<?php

declare(strict_types=1);

namespace SimpleCli\Options;

use SimpleCli\Attribute\Option;
use SimpleCli\SimpleCli;

trait Help
{
    #[Option('Display documentation of the current command.')]
    public bool $help = false;

    /**
     * Display help for the current command.
     *
     * @param SimpleCli $cli
     *
     * @return bool
     */
    public function displayHelp(SimpleCli $cli): bool
    {
        $arguments = [];

        foreach ($cli->getExpectedArguments() as $argument) {
            $arguments[$argument['property']] = $argument;
        }

        $restArgument = $cli->getExpectedRestArgument();

        if ($restArgument) {
            $arguments['...'.$restArgument['property']] = $restArgument;
        }

        $options = [];

        foreach ($cli->getExpectedOptions() as $option) {
            $option['names'] ??= [];
            $aliases = array_filter(
                $option['names'],
                static fn (string $name) => strlen($name) === 1,
            );
            $firstAlias = array_shift($aliases);
            $start = '    ';
            $option['names'] = array_map(
                static fn (string $name) => "--$name",
                array_filter(
                    $option['names'],
                    static fn (string $name) => strlen($name) !== 1,
                ),
            );

            if ($firstAlias) {
                $start = "-$firstAlias, ";
                $option['names'] = array_merge(
                    array_map(
                        static fn (string $alias) => "-$alias",
                        $aliases,
                    ),
                    $option['names'],
                );
            }

            $options[$start.implode(', ', $option['names'])] = $option;
        }

        $argumentsNames = array_keys($arguments);
        $optionsNames = array_keys($options);
        $length = max(array_merge(
            [0],
            array_map('strlen', $argumentsNames),
            array_map('strlen', $optionsNames),
        )) + 2;
        /** @psalm-suppress UnsafeInstantiation */
        $defaultInstance = new static(); // @phan-suppress-current-line PhanUndeclaredMethod

        $cli->writeLine('Usage:', 'brown');
        $cli->writeLine(
            '  '.basename($cli->getFile()).' '.$cli->getCommand().' [options] '.implode(
                ' ',
                array_map(
                    static fn (string $name) => "[<$name>]",
                    $argumentsNames,
                ),
            ),
        );

        $this->displayArguments($cli, $arguments, $length, $defaultInstance);
        $this->displayOptions($cli, $options, $length, $defaultInstance);

        return true;
    }

    /**
     * Display definitions of the arguments list.
     *
     * @param SimpleCli                   $cli
     * @param array<array<string, mixed>> $arguments
     * @param int                         $length
     * @param self                        $defaultInstance @phan-suppress-current-line PhanTypeMismatchDeclaredParam
     */
    protected function displayArguments(SimpleCli $cli, array $arguments, int $length, self $defaultInstance): void
    {
        if (count($arguments)) {
            $cli->writeLine();
            $cli->writeLine('Arguments:', 'brown');

            foreach ($arguments as $definition) {
                $property = (string) $definition['property'];
                $defaultValue = $this->getDefaultValue($defaultInstance, $property, $definition);

                $cli->displayVariable($length, $property, $definition, $defaultValue);
            }
        }
    }

    /**
     * @param SimpleCli                   $cli
     * @param array<array<string, mixed>> $options
     * @param int                         $length
     * @param self                        $defaultInstance @phan-suppress-current-line PhanTypeMismatchDeclaredParam
     */
    protected function displayOptions(SimpleCli $cli, array $options, int $length, self $defaultInstance): void
    {
        if (count($options)) {
            $cli->writeLine();
            $cli->writeLine('Options:', 'brown');

            foreach ($options as $name => $definition) {
                $name = (string) $name;
                $property = (string) $definition['property'];
                $defaultValue = $this->getDefaultValue($defaultInstance, $property, $definition);

                $cli->displayVariable($length, $name, $definition, $defaultValue);
            }
        }
    }

    protected function getDefaultValue(self $defaultInstance, string $property, array $definition): mixed
    {
        if (isset($defaultInstance->$property)) {
            return $defaultInstance->$property;
        }

        $type = explode('|', (string) ($definition['type'] ?? 'string'));
        $type = ltrim($type[0], '?');

        return match ($type) {
            'float' => 0.0,
            'int'   => 0,
            'array' => [],
            'bool'  => false,
            default => '',
        };
    }
}
