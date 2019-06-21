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
     */
    public function displayHelp(SimpleCli $cli)
    {
        $arguments = [];

        foreach ($cli->getExpectedArguments() as $argument) {
            $arguments[$argument['property']] = $argument;
        }

        $options = [];

        foreach ($cli->getExpectedOptions() as $option) {
            $aliases = array_filter($option['names'], function ($name) {
                return strlen($name) === 1;
            });
            $firstAlias = array_shift($aliases);
            $start = '    ';

            if ($firstAlias) {
                $start = "-$firstAlias, ";
                $option['names'] = array_merge(
                    array_map(function ($alias) {
                        return "-$alias";
                    }, $aliases),
                    array_map(function ($name) {
                        return "--$name";
                    }, array_filter($option['names'], function ($name) {
                        return strlen($name) !== 1;
                    }))
                );
            }

            $options[$start.implode(', ', $option['names'])] = $option;
        }

        $argumentsNames = array_keys($arguments);
        $optionsNames = array_keys($options);
        $length = (int) max(...array_map('strlen', $argumentsNames), ...array_map('strlen', $optionsNames)) + 2;
        $defaultInstance = new static();

        $cli->writeLine('Usage:', 'brown');
        $cli->writeLine('  '.$cli->getFile().' '.$cli->getCommand().' [options] '.implode(' ', array_map(function ($name) {
            return "[<$name>]";
        }, $argumentsNames)));
        $cli->writeLine();

        $cli->writeLine('Arguments:', 'brown');

        foreach ($arguments as $definition) {
            $property = $definition['property'];
            $cli->write('  ');
            $cli->write($property, 'green');
            $cli->write(str_repeat(' ', $length - strlen($property)));
            $cli->writeLine(str_replace(
                "\n",
                "\n".str_repeat(' ', $length + 2),
                $definition['description']."\n".
                $cli->colorize(str_pad($definition['values'] ?: $definition['type'], 16, ' ', STR_PAD_RIGHT), 'cyan').
                $cli->colorize('default: '.var_export($defaultInstance->$property, true), 'brown')
            ));
        }

        $cli->writeLine();

        $cli->writeLine('Options:', 'brown');

        foreach ($options as $name => $definition) {
            $property = $definition['property'];
            $cli->write('  ');
            $cli->write($name, 'green');
            $cli->write(str_repeat(' ', $length - strlen($name)));
            $cli->writeLine(str_replace(
                "\n",
                "\n".str_repeat(' ', $length + 2),
                $definition['description']."\n".
                $cli->colorize(str_pad($definition['values'] ?: $definition['type'], 16, ' ', STR_PAD_RIGHT), 'cyan').
                $cli->colorize('default: '.var_export($defaultInstance->$property, true), 'brown')
            ));
        }

        return true;
    }
}
