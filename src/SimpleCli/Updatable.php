<?php

declare(strict_types=1);

namespace SimpleCli;

interface Updatable
{
    public function getUpdateUrlForLatestVersion(): string;

    public function getUpdateUrlForNewestVersion(): string;

    public function getUpdateUrlForHighestVersion(): string;

    public function getUpdateUrlForVersion(string $version): string;
}
