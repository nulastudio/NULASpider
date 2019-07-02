<?php

use nulastudio\Spider\Spider;

$config = [
    'thread'              => 5,
    'UI'                  => false,
    'scan_urls'           => [
        'https://www.liesauer.net/blog/',
    ],
    'list_url_pattern'    => [
        '#^https://www.liesauer.net/blog/page/\d+/$#',
    ],
    'content_url_pattern' => [
        '#^https://www.liesauer.net/blog/post/.*?.html$#',
    ],
    'fields'              => [
        'title'   => 'css://.post-title>a',
        'meta'    => [
            'type'     => 'css',
            'selector' => '.post-meta',
            'fields'   => [
                'author'   => 'css://li:nth-child(1)>a',
                'time'     => 'css://li:nth-child(2)>time',
                'category' => 'css://li:nth-child(3)>a',
            ],
        ],
        'content' => 'css://.post-content>.md_content>textarea',
    ],
    'export'              => [
        'type' => 'json',
        'file' => DIR_DATA . '/blog.json',
    ],
];

$spider = new Spider($config);

$spider->use(User\Plugins\Buff::class);
$spider->use(User\Plugins\JsonExporter::class);

$spider->start();
