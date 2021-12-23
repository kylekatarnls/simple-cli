<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Attribute\Option;
use SimpleCli\Attribute\Values;
use SimpleCli\Command;
use SimpleCli\Options\Help;
use SimpleCli\SimpleCli;

class ValuesAttributeCommand implements Command
{
    use Help;

    #[Option('First option')]
    #[Values(['low', 'medium', 'high'])]
    public string $level = 'low';

    public function run(SimpleCli $cli): bool
    {
        return true;
    }
}
