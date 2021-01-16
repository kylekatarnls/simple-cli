<?php

declare(strict_types=1);

$replacements = [
    __DIR__.'/../vendor/symfony/polyfill-mbstring/bootstrap80.php' => [
        '$encoding ??= mb_internal_encoding();' => '$encoding = $encoding ?? mb_internal_encoding();',
    ],
];

foreach ($replacements as $file => $patterns) {
    $contents = @file_get_contents($file) ?: '';
    $newContents = strtr($contents, $patterns);

    if ($newContents !== $contents) {
        @file_put_contents($file, $newContents);
    }
}
