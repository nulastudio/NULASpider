<?php

use nulastudio\Collections\ConcurrentMemoryQueue;
use nulastudio\Collections\ConcurrentUniqueQueue;
use nulastudio\Log\FileLogger;
use nulastudio\Spider\Spider;

# ====================
# 配置
# ====================

$config = [
    'thread'              => 1,
    'UI'                  => false,
    'logger'              => new FileLogger(DIR_LOG . '/' . date('Y-m-d') . '.log'),
    'input_encoding'      => 'smart', // GIVEN_ENCODING, "auto", "smart", "handler"
    'fallback_encoding'   => '',
    'output_encoding'     => 'auto', // unsupported yet, always "UTF-8"
    'urlQueue'            => new ConcurrentUniqueQueue(),
    'downloadQueue'       => new ConcurrentMemoryQueue(),
    'processQueue'        => new ConcurrentMemoryQueue(),
    'scan_urls'           => [
        'http://localhost:8081/articles',
    ],
    'list_url_pattern'    => [
        '#http://localhost:8081/articles/\d+#',
    ],
    'content_url_pattern' => [
        '#http://localhost:8081/article/\d+#',
    ],
    'fields'              => [
        'title'    => 'xpath://.//article/h1/@innerText',
        'author'   => 'xpath://.//article/p[1]/@innerText',
        'content'  => 'xpath://.//article/p[2]/@innerText',
        'comments' => [
            'type'      => 'xpath',
            'area'      => ".//article",
            'selector'  => "./div[contains(@class, 'comment')]",
            'fields'    => [
                'author'  => "xpath://.//p[contains(@class, 'comment-author')]/@innerText",
                'time'    => "xpath://.//p[contains(@class, 'comment-time')]/@innerText",
                'content' => "xpath://.//p[contains(@class, 'comment-content')]/@innerText",
            ],
            'repeated'  => true,
            'recursive' => true,
        ],
    ],
    'export'              => [
        'type' => 'print',
    ],
];

$spider = new Spider($config);

$spider->on_fetch_page = function ($spider, $fields, $request, $response) {
    // 测试的话只看一个内容页就够了
    var_dump($fields);
    exit;
};

$spider->use(User\Plugins\Buff::class);
$spider->use(User\Plugins\PrintOutExporter::class);

$spider->start();
