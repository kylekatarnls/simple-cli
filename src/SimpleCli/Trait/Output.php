<?php

declare(strict_types=1);

namespace SimpleCli\Trait;

trait Output
{
    /** @var bool */
    protected bool $colorsSupported = true;

    /** @var bool */
    protected bool $muted = false;

    /** @var array<string, string> */
    protected array $colors = [
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

    /** @var array<string, string> */
    protected array $backgrounds = [
        'black'        => '40',
        'dark_gray'    => '48;5;59',
        'blue'         => '44',
        'light_blue'   => '48;5;63',
        'green'        => '42',
        'light_green'  => '48;5;40',
        'cyan'         => '46',
        'light_cyan'   => '48;5;87',
        'red'          => '41',
        'light_red'    => '48;5;168',
        'purple'       => '45',
        'light_purple' => '48;5;164',
        'brown'        => '43',
        'yellow'       => '48;5;108',
        'light_gray'   => '47',
        'white'        => '48;5;255',
    ];

    protected string $lastText = '';

    protected string $escapeCharacter = "\033";

    /**
     * List of the codes for each text color name.
     *
     * @return array<string, string>
     */
    public function getColors(): array
    {
        return $this->colors;
    }

    /**
     * List of the codes for each background color name.
     *
     * @return array<string, string>
     */
    public function getBackgrounds(): array
    {
        return $this->backgrounds;
    }

    /**
     * Returns true if the CLI program is muted (quiet).
     *
     * @return bool
     */
    public function isMuted(): bool
    {
        return $this->muted;
    }

    /**
     * Set the mute state.
     *
     * @param bool $muted
     */
    public function setMuted(bool $muted): void
    {
        $this->muted = $muted;
    }

    /**
     * Mute the program (no more output).
     */
    public function mute(): void
    {
        $this->setMuted(true);
    }

    /**
     * Unmute the program (enable output).
     */
    public function unmute(): void
    {
        $this->setMuted(false);
    }

    /**
     * Enable colors support in command line.
     */
    public function enableColors(): void
    {
        $this->colorsSupported = true;
    }

    /**
     * Disable colors support in command line.
     */
    public function disableColors(): void
    {
        $this->colorsSupported = false;
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
     * Set colors palette.
     *
     * @param array<string, string>|null $colors
     * @param array<string, string>|null $backgrounds
     */
    public function setColors(?array $colors = null, ?array $backgrounds = null): void
    {
        if ($colors) {
            $this->colors = $colors;
        }

        if ($backgrounds) {
            $this->backgrounds = $backgrounds;
        }
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
    public function colorize(string $text = '', ?string $color = null, ?string $background = null): string
    {
        if (!$this->colorsSupported || (!$color && !$background)) {
            return $text;
        }

        $color = $color ? $this->getColorCode($color) : '';
        $background = $background ? $this->getColorCode($background, $this->backgrounds) : '';

        return $color.$background.$text.$this->escapeCharacter.'[0m';
    }

    /**
     * Rewind CLI cursor $length characters behind, if $length is omitted, use the last written string length.
     *
     * @param int|null $length
     */
    public function rewind(?int $length = null): void
    {
        if ($this->isMuted()) {
            return;
        }

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
    public function write(string $text = '', ?string $color = null, ?string $background = null): void
    {
        if ($this->isMuted()) {
            return;
        }

        $this->lastText = $text;

        if ($color || $background) {
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
    public function writeLine(string $text = '', ?string $color = null, ?string $background = null): void
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
    public function rewrite(string $text = '', ?string $color = null, ?string $background = null): void
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
    public function rewriteLine(string $text = '', ?string $color = null, ?string $background = null): void
    {
        $this->write("\r$text", $color, $background);
    }

    /**
     * Output $text in red, add a new line and return false.
     *
     * @param string $text
     *
     * @return false
     */
    public function error(string $text = ''): bool
    {
        $this->writeLine($text, 'red');

        return false;
    }

    /**
     * @param string                          $color
     * @param array<string, string|null>|null $colors
     *
     * @return string
     */
    protected function getColorCode(string $color, ?array $colors = null): string
    {
        $colors = $colors ?: $this->colors;
        $color = $colors[$color] ?? $color;

        return $this->escapeCharacter.'['.$color.'m';
    }
}
