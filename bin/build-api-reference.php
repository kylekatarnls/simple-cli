<?php

use SimpleCli\SimpleCli;

include __DIR__.'/../vendor/autoload.php';

$doc = '';

foreach (get_class_methods(SimpleCli::class) as $method) {
    if (substr($method, 0, 2) === '__') {
        continue;
    }

    $reflextionMethod = new ReflectionMethod(SimpleCli::class, $method);

    $parameters = [];

    foreach ($reflextionMethod->getParameters() as $parameter) {
        $param = '';

        if ($type = $parameter->getType()) {
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

    $comment = trim($reflextionMethod->getDocComment());
    $return = $reflextionMethod->getReturnType();

    if (!$return && preg_match('/@return\s+(\S+)/', $comment, $match)) {
        $return = $match[1];
    }

    $doc .= '### '.$method.'('.implode(', ', $parameters).'): '.($return ?: 'mixed')."\n\n";

    $comment = trim($reflextionMethod->getDocComment());
    $comment = trim(preg_replace('/^\/\*+([\s\S]*)\*\/$/', '$1', $comment));
    $comment = trim(preg_replace('/^\s*\* /m', '', $comment));
    $comment = trim(preg_replace('/^\s*\*/m', '', $comment));
    $comment = trim(preg_replace('/^@(\w+)(.*)$/m', '', $comment));
    $comment = trim(preg_replace('/^(.*)$/m', '> $1', $comment));

    $doc .= "$comment\n\n";
}

$start = '<i start-api-reference></i>';
$end = '<i end-api-reference></i>';
$readme = __DIR__.'/../README.md';

file_put_contents($readme, preg_replace_callback('/'.preg_quote($start, '/').'[\s\S]*'.preg_quote($end, '/').'/', function () use ($doc, $start, $end) {
    return "$start\n\n$doc$end";
}, file_get_contents($readme)));
