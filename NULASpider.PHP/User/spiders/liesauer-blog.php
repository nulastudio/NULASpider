<?php

use nulastudio\Log\FileLogger;
use nulastudio\Networking\Http\Request;
use nulastudio\Networking\Http\UserAgent;
use nulastudio\Spider\Spider;

$config = [
    'thread'              => 5,
    'logger'              => new FileLogger(DIR_LOG . '/' . date('Y-m-d') . '.log'),
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
        'title'   => [
            'type'     => 'css',
            'selector' => '.post-title>a',
        ],
        'meta'    => [
            'type'     => 'css',
            'selector' => '.post-meta',
            'fields'   => [
                'author'   => [
                    'type'     => 'css',
                    'selector' => 'li:nth-child(1)>a',
                ],
                'time'     => [
                    'type'     => 'css',
                    'selector' => 'li:nth-child(2)>time',
                ],
                'category' => [
                    'type'     => 'css',
                    'selector' => 'li:nth-child(3)>a',
                ],
            ],
        ],
        'content' => [
            'type'     => 'css',
            'selector' => '.post-content>.md_content>textarea',
        ],
        // 'comments' => [
        //     'type'     => 'css',
        //     'selector' => '#comments>.comment-list>li',
        //     'repeated' => true,
        // ],
    ],
    // 'export'              => [
    //     'type'  => 'excel',
    //     'file'  => DIR_DATA . '/blog.xlsx',
    //     'sheet' => 'blog',
    // ],
    // 'export'              => [
    //     'type'  => 'print',
    // ],
    // 'export'              => [
    //     'type'     => 'pdo',
    //     'dsn'      => 'mysql:dbname=blog;host=localhost;charset=utf8',
    //     'username' => 'root',
    //     'password' => 'root',
    //     'table'    => 'blog',
    //     'options'  => [],
    // ],
];

$spider = new Spider($config);

$spider->on_start = function ($spider) {
    Request::getDefaultHeader()->setHeaders([
        'User-Agent' => UserAgent::USER_AGENTS['WIN10_X64_EDGE'],
    ]);
    // Request::getDefaultOption()->proxy = 'http://127.0.0.1:8888';
};

$spider->use(User\Plugins\Buff::class);
$spider->use(User\Plugins\Pipeline::class);
$spider->use(User\Plugins\ExcelExporter::class);
$spider->use(User\Plugins\PrintOutExporter::class);
$spider->use(User\Plugins\PDOExporter::class);

// 默认是不开启的，指定第二个参数可默认开启
$spider->use(User\Plugins\ProxyPool::class, [
    'http://127.0.0.1:8888',
]/*, true*/);

$spider->start();
