<?php

use SimpleCli\SimpleCli;

include __DIR__.'/../vendor/autoload.php';

$doc = '';

foreach (get_class_methods(SimpleCli::class) as $method) {
    if (substr($method, 0, 2) === '__') {
        continue;
    }

    $reflectionMethod = new ReflectionMethod(SimpleCli::class, $method);

    $parameters = [];

    foreach ($reflectionMethod->getParameters() as $parameter) {
        $param = '';

        if ($type = $parameter->getType()) {
            if ($type instanceof ReflectionNamedType) {
                $type = $type->getName();
            }

            $param .= "$type ";
        }

        $param .= '$'.$parameter->getName();

        try {
            if ($defaultValue = $parameter->getDefaultValue()) {
                $defaultValue = (string) var_export($defaultValue, true);
                $defaultValue = (string) preg_replace('/^\s*array\s*\(([\s\S]*)\)\s*$/', '[$1]', $defaultValue);
                $defaultValue = (string) preg_replace('/^\s*\[\s+\]$/', '[]', $defaultValue);

                $param .= " = $defaultValue";
            }
        } catch (ReflectionException $exception) {
        }

        $parameters[] = $param;
    }

    $comment = trim($reflectionMethod->getDocComment());
    $return = $reflectionMethod->getReturnType();

    if ($return instanceof ReflectionNamedType) {
        $return = $return->getName();
    }

    if (!$return && preg_match('/@return\s+(([^\s<]|<[^>]+>)+)/', $comment, $match)) {
        $return = $match[1];
    }

    $doc .= '### '.$method.'('.implode(', ', $parameters).'): '.($return ?: 'mixed')."\n\n";

    $comment = trim($reflectionMethod->getDocComment());
    $comment = trim(preg_replace('/^\/\*+([\s\S]*)\*\/$/', '$1', $comment));
    $comment = trim(preg_replace('/^\s*\* /m', '', $comment));
    $comment = trim(preg_replace('/^\s*\*/m', '', $comment));
    $comment = trim(preg_replace('/^@(\w+)(.*)(\n([ ]{4,}|\t+)\S.*)*$/m', '', $comment));
    $comment = trim(preg_replace('/^(.*)$/m', '> $1', $comment));

    $doc .= "$comment\n\n";
}

$start = '<i start-api-reference></i>';
$end = '<i end-api-reference></i>';
$readme = __DIR__.'/../README.md';

file_put_contents($readme, preg_replace_callback('/'.preg_quote($start, '/').'[\s\S]*'.preg_quote($end, '/').'/', function () use ($doc, $start, $end) {
    return "$start\n\n$doc$end";
}, file_get_contents($readme)));
