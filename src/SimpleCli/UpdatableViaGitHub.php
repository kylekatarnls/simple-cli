<?php

declare(strict_types=1);

namespace SimpleCli;

interface UpdatableViaGitHub
{
    public function getRepository(): string;

    public function getAssetName(?string $version = null): string;
}
