<?php

namespace Tests\SimpleCli\DemoApp;

use RuntimeException;
use SimpleCli\SimpleCli;
use SimpleCli\SimpleCliCommand\Create;

/**
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class DemoCli extends SimpleCli
{
    /** @var string */
    protected string $escapeCharacter = '[ESCAPE]';

    /** @var callable */
    protected $readlineFunction = [self::class, 'ask'];

    /**
     * @var callable|string
     *
     * @psalm-suppress NonInvariantDocblockPropertyType
     */
    protected $readlineCompletionRegisterFunction = [self::class, 'register'];

    /** @var string[] */
    protected array $readlineCompletionExtensions = [];

    /** @var callable[] */
    public static array $registered = [];

    /** @var callable|null */
    public static $answerer = null;

    /**
     * @return array<string, string|class-string<Command>>
     */
    public function getCommands(): array
    {
        return [
            'all'    => ArrayRestCommand::class,
            'hall'   => HelpedArrayRestCommand::class,
            'bad'    => BadCommand::class,
            'create' => Create::class,
            'rest'   => RestCommand::class,
            'foobar' => DemoCommand::class,
        ];
    }

    /**
     * @suppressWarnings(PHPMD.UndefinedVariable)
     *
     * @param callable $callback
     */
    public static function register(callable $callback): void
    {
        static::$registered[] = $callback;
    }

    /**
     * @param string $question
     *
     * @return mixed
     */
    public static function ask($question)
    {
        if (static::$answerer === null) {
            throw new RuntimeException('Set answer callback with setAnswerer() first.');
        }

        return (static::$answerer)($question);
    }

    public function setAnswerer(callable $answerer): void
    {
        static::$answerer = $answerer;
    }

    /**
     * @param string[] $readlineCompletionExtensions
     */
    public function setReadlineCompletionExtensions(array $readlineCompletionExtensions): void
    {
        $this->readlineCompletionExtensions = $readlineCompletionExtensions;
    }
}
