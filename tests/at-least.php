<?php

declare(strict_types=1);

exit(version_compare(PHP_VERSION, $argv[1], '>=') ? 0 : 1);
