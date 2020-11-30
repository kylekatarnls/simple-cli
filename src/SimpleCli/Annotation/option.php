<?php

declare(strict_types=1);

namespace SimpleCli\Annotation;

// phpcs:disable Squiz.Classes.ValidClassName

/**
 * Option to be set with --option-name or alias like -o.
 *
 * Syntax:
 *
 * Either:
 * @option names and aliases / description
 * Example:
 * @option debug d / Add debug information in the output
 *
 * Or:
 * @option / description
 * Example:
 * @option / Add debug information in the output
 *
 * Or:
 * @option names and aliases (optionally)
 * Description below
 * Example:
 * @option debug d
 * Add debug information in the output
 *
 * When names and aliases are not specified, the property name is used as option name and its first letter is used
 * as its alias if it's not yet in use.
 *
 * @SuppressWarnings(PHPMD.CamelCaseMethodName)
 */
class option
{
}
