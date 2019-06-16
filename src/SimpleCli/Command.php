<?php

namespace SimpleCli;

interface Command
{
    public function run(SimpleCli $cli, ...$parameters): bool;
}
