<?php

declare(strict_types=1);

namespace SimpleCli\SimpleCliCommand;

use SimpleCli\Annotation\argument; // @phan-suppress-current-line PhanUnreferencedUseNormal used as annotation
use SimpleCli\Command;
use SimpleCli\SimpleCli;

/**
 * Get the list of available colors and backgrounds.
 */
class Palette implements Command
{
    /**
     * @argument
     *
     * @var string
     */
    public $text = 'Hello world!';

    public function run(SimpleCli $cli): bool
    {
        $cli->writeLine('Colors:');

        foreach ($cli->getColors() as $name => $code) {
            $cli->write(str_pad($name, 18).str_pad($code, 10));
            $cli->writeLine($this->text, $name);
        }

        $cli->writeLine();
        $cli->writeLine('Backgrounds:');

        foreach ($cli->getBackgrounds() as $name => $code) {
            $cli->write(str_pad($name, 18).str_pad($code, 10));
            $cli->write($this->text, null, $name);
            $cli->writeLine();
        }

        return true;
    }
}
