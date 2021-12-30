<?php

declare(strict_types=1);

namespace SimpleCli\Command;

use Phar;
use SimpleCli\Attribute\Option;
use SimpleCli\CommandBase;
use SimpleCli\SimpleCli;

/**
 * Build the current program as a phar file.
 */
class SelfUpdate extends CommandBase
{
    protected const GITHUB_REPOSITORY = 'kylekatarnls/simple-cli';
    protected const URL_PATTERN = 'https://github.com/{repo}/releases/download/{version}/simple-cli.phar';
    protected const LATEST_URL = 'https://api.github.com/repos/{repo}/releases/latest/simple-cli.phar';

    #[Option(
        'Package version to install. Can be "latest" (latest stable), '.
        '"highest" (the highest semantic number including pre-releases), '.
        '"newest" (the most recent including pre-releases) '.
        'or an exact version number.',
    )]
    public string $version = 'latest';

    public function run(SimpleCli $cli): bool
    {
        $phar = class_exists(Phar::class) ? Phar::running(false) : null;
        $exactVersion = !in_array($this->version, ['latest', 'highest', 'newest'], true);

        if (!$phar) {
            return $cli->error(
                "Only PHAR files can be self-updated, for composer package, run:\n".
                'composer update "'.$cli->getPackageName().($exactVersion ? ':'.$this->version : '').'"',
            );
        }

        $url = strtr(static::LATEST_URL, ['{repo}' => static::GITHUB_REPOSITORY]);

        $cli->writeLine("Downloading $url", 'light_cyan');

        if (!copy($url, $phar)) {
            return $cli->error("Unable to update $phar");
        }

        $cli->writeLine("$phar updated", 'light_cyan');

        return true;
    }
}
