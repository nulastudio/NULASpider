<?php

$searchPath = __DIR__ . '/bin';
$projects   = [
    'NULASpider.PHP' => 'nulastudio.nulaspider',
];

foreach (getDEPSFiles($searchPath) as $deps) {
    foreach ($projects as $project => $fix) {
        if (strpos($deps, "{$project}.deps.json")) {
            fixDEPS($deps, $fix, $project);
            echo "{$deps} fixed." . PHP_EOL;
        }
    }
}

function getDEPSFiles($basePath)
{
    $deps = [];
    foreach (glob("{$basePath}/*") as $config) {
        foreach (glob("{$config}/*") as $framework) {
            foreach (glob("{$framework}/*.deps.json") as $file) {
                if (strpos($file, '.deps.json')) {
                    $deps[] = $file;
                }
            }
        }
    }
    return $deps;
}

function fixDEPS($depsFile, $search, $replace)
{
    $content = file_get_contents($depsFile);
    $content = str_replace($search, $replace, $content);
    file_put_contents($depsFile, $content);
}
