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

        if ($restArgument = $cli->getExpectedRestArgument()) {
            $arguments['...'.$restArgument['property']] = $restArgument;
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
        $length = (int) max(array_merge(array_map('strlen', $argumentsNames), array_map('strlen', $optionsNames))) + 2;
        $defaultInstance = new static();

        $cli->writeLine('Usage:', 'brown');
        $cli->writeLine('  '.$cli->getFile().' '.$cli->getCommand().' [options] '.implode(' ', array_map(function ($name) {
            return "[<$name>]";
        }, $argumentsNames)));

        $this->displayArguments($cli, $arguments, $length, $defaultInstance);
        $this->displayOptions($cli, $options, $length, $defaultInstance);

        return true;
    }

    /**
     * @param SimpleCli $cli
     * @param array     $arguments
     * @param int       $length
     * @param self      $defaultInstance
     */
    protected function displayArguments(SimpleCli $cli, array $arguments, int $length, self $defaultInstance): void
    {
        if (count($arguments)) {
            $cli->writeLine();
            $cli->writeLine('Arguments:', 'brown');

            foreach ($arguments as $definition) {
                $property = (string) $definition['property'];
                $cli->write('  ');
                $cli->write($property, 'green');
                $cli->write(str_repeat(' ', $length - strlen($property)));
                $cli->writeLine(str_replace(
                    "\n",
                    "\n".str_repeat(' ', $length + 2),
                    $definition['description']."\n".
                    $cli->colorize(str_pad($definition['values'] ?: $definition['type'], 16, ' ', STR_PAD_RIGHT), 'cyan').
                    $cli->colorize('default: '.$this->getValueExport($defaultInstance->$property), 'brown')
                ));
            }
        }
    }

    /**
     * @param SimpleCli $cli
     * @param array     $options
     * @param int       $length
     * @param self      $defaultInstance
     */
    protected function displayOptions(SimpleCli $cli, array $options, int $length, self $defaultInstance): void
    {
        if (count($options)) {
            $cli->writeLine();
            $cli->writeLine('Options:', 'brown');

            foreach ($options as $name => $definition) {
                $name = (string) $name;
                $property = (string) $definition['property'];
                $cli->write('  ');
                $cli->write($name, 'green');
                $cli->write(str_repeat(' ', $length - strlen($name)));
                $cli->writeLine(str_replace(
                    "\n",
                    "\n".str_repeat(' ', $length + 2),
                    $definition['description']."\n".
                    $cli->colorize(str_pad($definition['values'] ?: $definition['type'], 16, ' ', STR_PAD_RIGHT), 'cyan').
                    $cli->colorize('default: '.$this->getValueExport($defaultInstance->$property), 'brown')
                ));
            }
        }
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
        $value = (string) preg_replace('/^\s*\[\s+\]$/', '[]', $value);

        return $value;
    }
}
