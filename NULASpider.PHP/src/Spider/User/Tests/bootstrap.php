<?php

$pattern = '*';

// var_dump(rglob(__DIR__ . "/{$pattern}", GLOB_BRACE));

// foreach (rglob(__DIR__ . "/{$pattern}", GLOB_BRACE) as $file) {
//     if ($file === __FILE__ || strtolower(substr($file, -4)) !== '.php') {
//         continue;
//     }
//     echo "testing {$file}\n";
//     include $file;
// }

function tree($directory)
{
    $result = [];
    $dir    = dir($directory);
    while ($file = $dir->read()) {
        var_dump($file);
        // if (($file == '.') || ($file == '..')) {
        //     continue;
        // }
        // if ((is_dir("{$directory}/{$file}"))) {
        //     if (($file != '.') && ($file != '..')) {
        //         $result = array_merge($result, tree("{$directory}/{$file}"));

        //     }
        // } else {
        //     $result[] = $file;
        // }
    }
    $dir->close();
    return $result;
}

var_dump(tree(__DIR__));
