<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

use Closure;
use RuntimeException;
use SimpleCli\Writer;

trait Input
{
    /** @var Closure|callable|string[]|null */
    protected $currentCompletion = null;

    protected Closure|string|array $readlineFunction = 'readline';

    protected Closure|string|array $readlineCompletionRegisterFunction = 'readline_completion_function';

    /** @var string[] */
    protected array $readlineCompletionExtensions = ['readline'];

    protected string $stdinStream = 'php://stdin';

    protected function recordAutocomplete(): void
    {
        foreach ($this->readlineCompletionExtensions as $extension) {
            if (!extension_loaded($extension)) {
                return;
            }
        }

        if (is_callable($this->readlineCompletionRegisterFunction)) {
            ($this->readlineCompletionRegisterFunction)([$this, 'autocomplete']);
        }
    }

    /**
     * Get possible completions for a given start.
     *
     * @param string $start
     *
     * @return string[]
     */
    public function autocomplete(string $start = ''): array
    {
        if (is_array($this->currentCompletion)) {
            $length = strlen($start);

            return array_values(
                array_filter(
                    $this->currentCompletion,
                    function ($suggestion) use ($length, $start) {
                        /** @psalm-suppress PossiblyInvalidCast */
                        return substr((string) $suggestion, 0, $length) === $start;
                    }
                )
            );
        }

        return $this->currentCompletion ? ($this->currentCompletion)($start) : [];
    }

    /**
     * Ask the user $prompt and return the CLI input.
     *
     * @param string                         $prompt
     * @param Closure|callable|string[]|null $completion
     *
     * @return string
     */
    public function read(string $prompt, Closure|callable|array|null $completion = null): string
    {
        $this->currentCompletion = $completion;

        return ($this->readlineFunction)($prompt);
    }

    /**
     * Ask secretly (keep user input hidden) $prompt and return the CLI input.
     *
     * @param string $prompt
     * @param string $afterPrompt
     *
     * @return string
     */
    public function readHidden(string $prompt = '', string $afterPrompt = PHP_EOL): string
    {
        $secret = $this->readHiddenPrompt($prompt);
        $this->displayMessage($afterPrompt);

        return $secret === false || $secret === null ? '' : $secret;
    }

    /**
     * Get the initial stdin content as receive by the command using:
     * echo "foobar" | command
     * Or:
     * command < some-file.txt
     * Returns an empty string if no input passed.
     *
     * @return string
     */
    public function getStandardInput(): string
    {
        $stdin = '';
        $stream = fopen($this->stdinStream, 'r');
        $read = [$stream];
        $write = null;
        $except = null;

        if (stream_select($read, $write, $except, 0) === 1) {
            while ($line = fgets($stream)) {
                $stdin .= $line;
            }
        }

        fclose($stream);

        return $stdin;
    }

    private function displayMessage(string $message): void
    {
        if ($this instanceof Writer) {
            $this->write($message);

            return;
        }

        echo $message;
    }

    private function readHiddenPrompt(string $prompt = ''): string|null|false
    {
        // @codeCoverageIgnoreStart
        if (preg_match('/^win/i', PHP_OS)) {
            $this->displayMessage($prompt);

            return exec(__DIR__.'/../../../bin/prompt_win.bat');
        }
        // @codeCoverageIgnoreEnd

        $exec = $this->execFunction ?? 'shell_exec';

        if (rtrim($exec("/usr/bin/env bash -c 'echo OK'") ?: '') !== 'OK') {
            throw new RuntimeException("Can't invoke bash");
        }

        $result = $exec(
            "/usr/bin/env bash -c 'read -s -p \"".
            addslashes($prompt).
            "\" secret && echo \$secret'",
        );

        return is_string($result) ? preg_replace('/\n$/', '', $result) : $result;
    }
}
