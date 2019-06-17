<?php

namespace SimpleCli;

interface Command
{
    public function getDescription(): string;

    public function run(SimpleCli $cli, ...$parameters): bool;
}
