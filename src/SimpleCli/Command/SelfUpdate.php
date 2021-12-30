<?php

declare(strict_types=1);

namespace SimpleCli\Command;

use Phar;
use RuntimeException;
use SimpleCli\Attribute\Option;
use SimpleCli\CommandBase;
use SimpleCli\SimpleCli;
use SimpleCli\Updatable;

/**
 * Build the current program as a phar file.
 */
class SelfUpdate extends CommandBase
{
    protected const GITHUB_DOWNLOAD_URL_PATTERN = 'https://github.com/{repo}/releases/download/{version}/{asset}';

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

        $url = $this->getUrl($cli);
        $cli->writeLine("Downloading $url", 'light_cyan');

        if (!copy($url, $phar)) {
            return $cli->error("Unable to update $phar");
        }

        $cli->writeLine("$phar updated", 'light_cyan');

        return true;
    }

    protected function getUrl(SimpleCli $cli): string
    {
        if ($cli instanceof Updatable) {
            return match ($this->version) {
                'latest'  => $cli->getUpdateUrlForLatestVersion(),
                'highest' => $cli->getUpdateUrlForHighestVersion(),
                'newest'  => $cli->getUpdateUrlForNewestVersion(),
                default   => $cli->getUpdateUrlForVersion($this->version),
            };
        }

        $repository = $cli->getRepository();
        $version = match ($this->version) {
            'latest'  => $this->getGitHubLatestVersion($repository),
            'highest' => $this->getGitHubHighestVersion($repository),
            'newest'  => $this->getGitHubNewestVersion($repository),
            default   => $this->version,
        };

        return strtr(static::GITHUB_DOWNLOAD_URL_PATTERN, [
            '{repo}'    => $cli->getRepository(),
            '{asset}'   => $cli->getAssetName($version),
            '{version}' => $version,
        ]);
    }

    protected function getGitHubReleases(string $repository, string $suffix = ''): array
    {
        return json_decode(file_get_contents("https://api.github.com/repos/$repository/releases$suffix"), true);
    }

    protected function getTag(mixed $tag, string $error): string
    {
        if (!is_string($tag)) {
            throw new RuntimeException($error);
        }

        return $tag;
    }

    protected function getGitHubLatestVersion(string $repository): string
    {
        return $this->getTag(
            $this->getGitHubReleases($repository, '/latest')['tag_name'] ?? null,
            "No latest release found in $repository GitHub repository.",
        );
    }

    protected function getGitHubHighestVersion(string $repository): string
    {
        $releases = $this->getGitHubReleases($repository);
        usort($releases, static fn (array $a, array $b) => version_compare(
            $b['tag_name'] ?? '0.0.0',
            $a['tag_name'] ?? '0.0.0',
        ));
        $tag = null;

        foreach ($releases as $release) {
            $tag = $release['tag_name'] ?? null;

            break;
        }

        return $this->getTag($tag, "No highest release found in $repository GitHub repository.");
    }

    protected function getGitHubNewestVersion(string $repository): string
    {
        $releases = $this->getGitHubReleases($repository);
        usort($releases, static fn (array $a, array $b) => $a['published_at'] <=> $b['published_at']);
        $tag = null;

        foreach ($releases as $release) {
            $tag = $release['tag_name'] ?? null;

            break;
        }

        return $this->getTag($tag, "No newest release found in $repository GitHub repository.");
    }
}
