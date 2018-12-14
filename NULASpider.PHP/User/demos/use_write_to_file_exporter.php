<?php

use nulastudio\Spider\Spider;

// 配置
$config = [
    'export' => [
        'type' => 'file',
        'file' => 'path/to/file',
    ],
];

$spider = new Spider($config);

$spider->use(User\Plugins\WriteToFileExporter::class);

$spider->run();
