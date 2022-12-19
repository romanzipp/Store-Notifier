<?php

use Carbon\Carbon;
use romanzipp\ModelDoc\Services\DocumentationGenerator;

require __DIR__ . '/vendor/autoload.php';

if ( ! function_exists('base_path')) {
    function base_path(string $path): string
    {
        return __DIR__ . '/' . $path;
    }
}

if ( ! function_exists('now')) {
    function now()
    {
        return Carbon::now();
    }
}

if ( ! function_exists('config')) {
    function config($attr)
    {
        return match ($attr) {
            'model-doc.ignore' => [],
            'model-doc.attributes.enabled' => true,
            'model-doc.accessors.enabled' => true,
            'model-doc.scopes.enabled' => true,
            'model-doc.scopes.ignore' => [],
            'model-doc.relations.enabled' => true,
            'model-doc.relations.counts.enabled' => true,
            'model-doc.fail_when_empty' => false,
            default => dd($attr)
        };
    }
}

DocumentationGenerator::usePath(fn () => __DIR__ . '/src/Models');

$generator = new DocumentationGenerator();
$models = $generator->collectModels();

foreach ($models as $model) {
    $generator->generate($model);
}
