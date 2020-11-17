<?php

declare(strict_types=1);

namespace SimpleCli\Traits;

use Closure;

trait Input
{
    /** @var Closure|callable|string[]|null */
    protected $currentCompletion = null;

    /** @var callable */
    protected $readlineFunction = 'readline';

    /** @var callable|string */
    protected $readlineCompletionRegisterFunction = 'readline_completion_function';

    /** @var string[] */
    protected $readlineCompletionExtensions = ['readline'];

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
    public function read($prompt, $completion = null): string
    {
        $this->currentCompletion = $completion;

        return ($this->readlineFunction)($prompt);
    }
}
