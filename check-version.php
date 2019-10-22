<?php

if (version_compare(PHP_VERSION, $argv[1], '>=')) {
    echo 'Version not supported.';
    exit(0);
}

exit(1);
