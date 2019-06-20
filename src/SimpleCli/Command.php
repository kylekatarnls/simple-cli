<?php

declare(strict_types=1);

namespace SimpleCli;

interface Command
{
    public function run(SimpleCli $cli): bool;
}
