<?php

/**
 * This configuration will be read and overlaid on top of the
 * default configuration. Command-line arguments will be applied
 * after this file is read.
 */
return [
    'target_php_version' => '8.0',
    'directory_list' => [
        'src',
        'vendor',
    ],
    'exclude_file_regex' => '@^vendor/(vimeo/psalm/.*|.*/(tests?|Tests?)/)@',
    'exclude_analysis_directory_list' => [
        'vendor',
    ],
    'plugins' => [
        'AlwaysReturnPlugin',
        'UnreachableCodePlugin',
        'DollarDollarPlugin',
        'DuplicateArrayKeyPlugin',
        'PregRegexCheckerPlugin',
        'PrintfCheckerPlugin',
    ],
];
