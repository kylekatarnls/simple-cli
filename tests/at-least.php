<?php

declare(strict_types=1);

$atLeast = version_compare(PHP_VERSION, $argv[1], '>=');
echo PHP_VERSION.($atLeast ? ' >= ' : ' < ').$argv[1]."\n";
exit($atLeast ? 0 : 1);
