<?php

declare(strict_types=1);

namespace SimpleCli\Trait;

trait GitHubUpdater
{
    public function getUpdateUrlForLatestVersion(): string
    {
        return '';
    }

    public function getUpdateUrlForNewestVersion(): string
    {
        return '';
    }

    public function getUpdateUrlForHighestVersion(): string
    {
        return '';
    }

    public function getUpdateUrlForVersion(string $version): string
    {
        return '';
    }
}
