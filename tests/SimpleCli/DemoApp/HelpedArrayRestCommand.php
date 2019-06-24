<?php

namespace Tests\SimpleCli\DemoApp;

use SimpleCli\Options\Help;

/**
 * This is a demo.
 */
class HelpedArrayRestCommand extends ArrayRestCommand
{
    use Help;
}
