<?php

declare(strict_types=1);

namespace SimpleCli\Options;

use SimpleCli\SimpleCli;

trait Help
{
    /**
     * @option
     *
     * Display documentation of the current command.
     *
     * @var bool
     */
    public $help = false;

    /**
     * Display help for the current command.
     *
     * @param SimpleCli $cli
     *
     * @return bool
     */
    public function displayHelp(SimpleCli $cli)
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
            $aliases = array_filter(
                $option['names'],
                function (string $name) {
                    return strlen($name) === 1;
                }
            );
            $firstAlias = array_shift($aliases);
            $start = '    ';

            if ($firstAlias) {
                $start = "-$firstAlias, ";
                $option['names'] = array_merge(
                    array_map(
                        function (string $alias) {
                            return "-$alias";
                        },
                        $aliases
                    ),
                    array_map(
                        function (string $name) {
                            return "--$name";
                        },
                        array_filter(
                            $option['names'],
                            function (string $name) {
                                return strlen($name) !== 1;
                            }
                        )
                    )
                );
            }

            $options[$start.implode(', ', $option['names'])] = $option;
        }

        $argumentsNames = array_keys($arguments);
        $optionsNames = array_keys($options);
        $length = (int) max(array_merge(array_map('strlen', $argumentsNames), array_map('strlen', $optionsNames))) + 2;
        /** @psalm-suppress UnsafeInstantiation */
        $defaultInstance = new static(); // @phan-suppress-current-line PhanUndeclaredMethod

        $cli->writeLine('Usage:', 'brown');
        $cli->writeLine(
            '  '.basename($cli->getFile()).' '.$cli->getCommand().' [options] '.implode(
                ' ',
                array_map(
                    function (string $name) {
                        return "[<$name>]";
                    },
                    $argumentsNames
                )
            )
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
                $cli->displayVariable($length, (string) $property, $definition, $defaultInstance->$property);
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
                $cli->displayVariable($length, (string) $name, $definition, $defaultInstance->$property);
            }
        }
    }
}
