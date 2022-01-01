<?php

declare(strict_types=1);

namespace SimpleCli\Command;

use Phar;
use SimpleCli\Attribute\Option;
use SimpleCli\CommandBase;
use SimpleCli\Exception\RuntimeException;
use SimpleCli\SimpleCli;
use SimpleCli\Updatable;
use SimpleCli\Widget\ProgressBar;

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
        $phar = $this->getRunningPhar();
        $exactVersion = !in_array($this->version, ['latest', 'highest', 'newest'], true);

        if (!$phar) {
            return $cli->error(
                "Only PHAR files can be self-updated, for composer package, run:\n".
                'composer update "'.$cli->getPackageName().($exactVersion ? ':'.$this->version : '').'"',
            );
        }

        $url = $this->getUrl($cli);
        $cli->writeLine("Downloading $url", 'light_cyan');

        if (!is_writable($phar)) {
            return $cli->error("$phar is not writable");
        }

        if (!$this->download($cli, $url, $phar)) {
            return $cli->error("Unable to update $phar");
        }

        $cli->writeLine("$phar updated", 'light_cyan');

        return true;
    }

    /**
     * @SuppressWarnings(PHPMD.StaticAccess)
     */
    protected function getRunningPhar(): ?string
    {
        return class_exists(Phar::class) ? Phar::running(false) : null;
    }

    protected function getUserAgent(SimpleCli $cli): string
    {
        return 'PHP '.PHP_VERSION.' curl '.$cli->getDisplayName().'/'.$cli->getVersion();
    }

    /**
     * @return resource
     */
    protected function getStreamContext(SimpleCli $cli)
    {
        return stream_context_create([
            'http' => [
                'method'           => 'GET',
                'protocol_version' => 1.1,
                'follow_location'  => 1,
                'header'           => 'User-Agent: '.$this->getUserAgent($cli)."\r\n",
            ],
        ]);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    protected function download(SimpleCli $cli, string $from, string $to): bool
    {
        $error = null;

        if (function_exists('curl_init')) {
            $progressBar = new ProgressBar($cli);
            $progressBar->start();
            $curlHandle = curl_init();
            curl_setopt($curlHandle, CURLOPT_URL, $from);
            curl_setopt($curlHandle, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlHandle, CURLOPT_USERAGENT, $this->getUserAgent($cli));
            curl_setopt(
                $curlHandle,
                CURLOPT_PROGRESSFUNCTION,
                /** @param resource $resource */
                static fn ($resource, int $downloadSize, int $downloaded) => $progressBar->setValue(
                    $downloadSize ? ($downloaded / $downloadSize) : 0.0,
                ),
            );
            curl_setopt($curlHandle, CURLOPT_NOPROGRESS, false);
            curl_setopt($curlHandle, CURLOPT_HEADER, 0);
            $content = curl_exec($curlHandle);
            $error = curl_error($curlHandle);
            curl_close($curlHandle);
            $progressBar->end();

            if (is_string($content) && $content !== '' && file_put_contents($to, $content)) {
                return true;
            }
        }

        if (copy($from, $to, $this->getStreamContext($cli))) {
            return true;
        }

        if ($error) {
            $cli->error($error);
        }

        return false;
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
            'latest'  => $this->getGitHubLatestVersion($cli, $repository),
            'highest' => $this->getGitHubHighestVersion($cli, $repository),
            'newest'  => $this->getGitHubNewestVersion($cli, $repository),
            default   => $this->version,
        };

        // @phan-suppress-next-line PhanParamSuspiciousOrder
        return strtr(static::GITHUB_DOWNLOAD_URL_PATTERN, [
            '{repo}'    => $cli->getRepository(),
            '{asset}'   => $cli->getAssetName($version),
            '{version}' => $version,
        ]);
    }

    protected function getGitHubReleases(SimpleCli $cli, string $repository, string $suffix = ''): array
    {
        return json_decode(
            file_get_contents(
                "https://api.github.com/repos/$repository/releases$suffix",
                context: $this->getStreamContext($cli),
            ),
            true,
        );
    }

    protected function getTag(mixed $tag, string $error, int $code): string
    {
        if (!is_string($tag)) {
            throw new RuntimeException($error, $code);
        }

        return $tag;
    }

    protected function getGitHubLatestVersion(SimpleCli $cli, string $repository): string
    {
        return $this->getTag(
            $this->getGitHubReleases($cli, $repository, '/latest')['tag_name'] ?? null,
            "No latest release found in $repository GitHub repository.",
            RuntimeException::LATEST_RELEASE_NOT_FOUND,
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    protected function getGitHubHighestVersion(SimpleCli $cli, string $repository): string
    {
        $releases = $this->getGitHubReleases($cli, $repository);
        usort($releases, static fn (array $a, array $b) => version_compare(
            $b['tag_name'] ?? '0.0.0',
            $a['tag_name'] ?? '0.0.0',
        ));
        $tag = null;

        foreach ($releases as $release) {
            $tag = $release['tag_name'] ?? null;

            break;
        }

        return $this->getTag(
            $tag,
            "No highest release found in $repository GitHub repository.",
            RuntimeException::HIGHEST_RELEASE_NOT_FOUND,
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    protected function getGitHubNewestVersion(SimpleCli $cli, string $repository): string
    {
        $releases = $this->getGitHubReleases($cli, $repository);
        usort($releases, static fn (array $a, array $b) => $a['published_at'] <=> $b['published_at']);
        $tag = null;

        foreach ($releases as $release) {
            $tag = $release['tag_name'] ?? null;

            break;
        }

        return $this->getTag(
            $tag,
            "No newest release found in $repository GitHub repository.",
            RuntimeException::NEWEST_RELEASE_NOT_FOUND,
        );
    }
}
