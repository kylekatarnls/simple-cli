<?php

declare(strict_types=1);

namespace SimpleCli\Annotation;

// phpcs:disable Squiz.Classes.ValidClassName

/**
 * Remaining arguments after all specific arguments filled.
 *
 * Syntax can be either:
 *
 * @rest / description
 * Example:
 * @rest / File path
 *
 * Or:
 * @rest
 * Description below
 * Example:
 * @rest
 * File path
 *
 * @SuppressWarnings(PHPMD.CamelCaseClassName)
 */
class rest
{
}
