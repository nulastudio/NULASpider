<?php

use nulastudio\Spider\Spider;

$config = [
    'thread'              => 5,
    'UI'                  => false,
    'scan_urls'           => [
        'http://jsonplaceholder.typicode.com/posts/1/comments',
    ],
    'list_url_pattern'    => [],
    'content_url_pattern' => [
        'http://jsonplaceholder.typicode.com/posts/1/comments',
    ],
    'fields'              => [
        'names'  => 'jsonpath://$..name',
        'bodies' => 'jsonpath://$..body',
    ],
    'export'              => [
        'type' => 'print',
    ],
];

$spider = new Spider($config);

$spider->use(User\Plugins\Buff::class);
$spider->use(User\Plugins\PrintOutExporter::class);

$spider->start();
