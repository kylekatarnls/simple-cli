<?php

namespace SimpleCli;

use SimpleCli\Command\Usage;
use SimpleCli\Command\Version;

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

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getCommand(): string
    {
        return $this->command;
    }

    public function getCommands(): array
    {
        return [];
    }

    public function getPackageName(): string
    {
        return '';
    }

    public function getVersionDetails(): string
    {
        return '';
    }

    public function getVersion()
    {
        $packageName = $this->getPackageName();
        $start = $packageName === '' ? '' : $this->colorize($packageName, 'green').' version ';

        return $start.$this->colorize($this->getInstalledPackageVersion($packageName), 'brown').$this->getVersionDetails();
    }

    public function getInstalledPackages()
    {
        $installedJson = __DIR__.'/../../../../composer/installed.json';
        $installedData = file_exists($installedJson) ? @json_decode(file_get_contents($installedJson) ?: '') : null;

        return $installedData ?: [];
    }

    public function getInstalledPackage($name)
    {
        foreach ($this->getInstalledPackages() as $package) {
            if ($package->name === $name) {
                return $package;
            }
        }

        return null;
    }

    public function getInstalledPackageVersion($name)
    {
        $package = $this->getInstalledPackage($name);
        $version = $package ? $package->version : null;

        return $version ?: 'unknown';
    }

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
     * Set a custom string for escape command in CLI strings.
     *
     * @param string $escapeCharacter
     */
    public function setEscapeCharacter(string $escapeCharacter): void
    {
        $this->escapeCharacter = $escapeCharacter;
    }

    public function autocomplete(string $start = '')
    {
        if (is_array($this->currentCompletion)) {
            $length = strlen($start);

            return array_filter($this->currentCompletion, function ($suggestion) use ($length, $start) {
                return substr($suggestion, 0, $length) === $start;
            });
        }

        return $this->currentCompletion ? ($this->currentCompletion)($start) : [];
    }

    protected function getColorCode(string $color, array $colors = null)
    {
        $colors = $colors ?: $this->colors;
        $color = $colors[$color] ?? $color;

        return $this->escapeCharacter.'['.$color.'m';
    }

    protected function colorize(string $text = '', string $color = null, string $background = null)
    {
        if (!$color && !$background) {
            return $text;
        }

        $color = $color ? $this->getColorCode($color) : '';
        $background = $background ? $this->getColorCode($background, $this->backgrounds) : '';

        return $color.$background.$text.$this->escapeCharacter.'[0m';
    }

    public function read($prompt, $completion = null)
    {
        $this->currentCompletion = $completion;

        return readline($prompt);
    }

    public function rewind(int $length = null): void
    {
        if ($length === null) {
            $length = strlen($this->lastText);
        }

        echo $this->escapeCharacter.'['.$length.'D';
    }

    public function write(string $text = '', string $color = null): void
    {
        $this->lastText = $text;

        if ($color) {
            $text = $this->colorize($text, $color);
        }

        echo $text;
    }

    public function writeLine(string $text = '', string $color = null): void
    {
        $this->write("$text\n", $color);
    }

    public function rewrite(string $text = '', string $color = null): void
    {
        $this->rewind();
        $this->write($text, $color);
    }

    public function rewriteLine(string $text = '', string $color = null): void
    {
        $this->write("\r$text", $color);
    }

    public function getAvailableCommands()
    {
        return array_filter(array_merge([
            'list'    => Usage::class,
            'version' => Version::class,
        ], $this->getCommands()), 'boolval');
    }

    public function __invoke(string $file, string $command = 'list', ...$parameters): bool
    {
        $this->file = $file;
        $this->command = $command;

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

        return $commander->run($this, ...$parameters);
    }
}
