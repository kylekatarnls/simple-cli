<?php

declare(strict_types=1);

namespace SimpleCli\Widget\Trait;

trait TableOutput
{
    protected ?string $output = null;

    protected function resetOutput(): void
    {
        $this->output = '';
    }

    protected function addToOutput(string $content): void
    {
        /** @psalm-suppress PossiblyNullOperand */
        $this->output .= $content;
    }
}
