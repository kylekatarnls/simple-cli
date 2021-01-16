<?php

declare(strict_types=1);

namespace SimpleCli;

class CliConfig
{
    public const NONE_COLOR = 'none';

    /** @var bool */
    protected $colorsSupported = true;

    /** @var bool */
    protected $muted = false;

    /** @var array<string, string> */
    protected $colors = [
        self::NONE_COLOR => '0',
        'black'          => '0;30',
        'dark_gray'      => '1;30',
        'blue'           => '0;34',
        'light_blue'     => '1;34',
        'green'          => '0;32',
        'light_green'    => '1;32',
        'cyan'           => '0;36',
        'light_cyan'     => '1;36',
        'red'            => '0;31',
        'light_red'      => '1;31',
        'purple'         => '0;35',
        'light_purple'   => '1;35',
        'brown'          => '0;33',
        'yellow'         => '1;33',
        'light_gray'     => '0;37',
        'white'          => '1;37',
    ];

    /** @var array<string, string> */
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

    /** @var string */
    protected $escapeCharacter = "\033";

    /** @var string */
    protected $escapeCharacterRegex = '\\033';

    /** @var string */
    protected $colorCodePattern = '%s[%sm';

    /** @var string */
    protected $rewindCodePattern = '%s[%sD';

    public function getEscapeCharacter(): string
    {
        return $this->escapeCharacter;
    }

    public function getEscapeCharacterRegex(): string
    {
        return $this->escapeCharacterRegex;
    }

    /**
     * @return array<string, string>
     */
    public function getBackgrounds(): array
    {
        return $this->backgrounds;
    }

    /**
     * @return array<string, string>
     */
    public function getColors(): array
    {
        return $this->colors;
    }

    public function getRewindCodePattern(): string
    {
        return $this->rewindCodePattern;
    }

    public function getColorCodePattern(): string
    {
        return $this->colorCodePattern;
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
     * Return true if colors are supported in current config.
     *
     * @return bool
     */
    public function areColorsSupported(): bool
    {
        return $this->colorsSupported;
    }

    /**
     * Set a custom string for escape command in CLI strings.
     *
     * @param string $escapeCharacter
     */
    public function setEscapeCharacter(string $escapeCharacter, ?string $escapeCharacterRegex = null): void
    {
        $this->escapeCharacter = $escapeCharacter;
        $this->escapeCharacterRegex = $escapeCharacterRegex ?? preg_quote($escapeCharacter, '/');
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
            $this->colors = array_merge([self::NONE_COLOR => '0'], $colors);
        }

        if ($backgrounds) {
            $this->backgrounds = $backgrounds;
        }
    }

    /**
     * Return a color-stop CLI marker.
     *
     * @return string
     */
    public function uncolorize(): string
    {
        return $this->getColorCode(self::NONE_COLOR);
    }

    public function getNoneColor(): ?string
    {
        return $this->colors[self::NONE_COLOR] ?? null;
    }

    /**
     * @param string                          $color
     * @param array<string, string|null>|null $colors
     *
     * @return string
     */
    protected function getColorCode(string $color, ?array $colors = null): string
    {
        if (!$this->colorsSupported) {
            return '';
        }

        $colors = $colors ?: $this->colors;
        $color = $colors[$color] ?? $color;

        return sprintf($this->colorCodePattern, $this->escapeCharacter, $color);
    }
}
