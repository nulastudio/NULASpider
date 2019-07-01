<?php

use nulastudio\Collections\ConcurrentMemoryQueue;
use nulastudio\Collections\UniqueQueue;
use nulastudio\Log\FileLogger;
use nulastudio\Spider\Spider;

# ====================
# 配置
# ====================

$config = [
    'thread'              => 5,
    'UI'                  => false,
    'logger'              => new FileLogger(DIR_LOG . '/' . date('Y-m-d') . '.log'),
    'input_encoding'      => 'smart', // GIVEN_ENCODING, "auto", "smart", "handler"
    'fallback_encoding'   => '',
    'output_encoding'     => 'auto', // unsupported yet, always "UTF-8"
    'urlQueue'            => new UniqueQueue(),
    'downloadQueue'       => new ConcurrentMemoryQueue(),
    'processQueue'        => new ConcurrentMemoryQueue(),
    'scan_urls'           => [
        'https://news.ycombinator.com/item?id=14936025',
    ],
    'list_url_pattern'    => [
        '#https://news.ycombinator.com/news\?p=\d+#',
    ],
    'content_url_pattern' => [
        '#https://news.ycombinator.com/item\?id=\d+#',
    ],
    'fields'              => [
        'title'    => 'css://.title>a@innerText',
        'comments' => [
            'type'     => 'css',
            'selector' => '.comment-tree>tr',
            'fields'   => [
                'ind'     => 'css://.ind>img@width',
                'author'  => 'css://.hnuser@innerText',
                'time'    => 'css://.age>a@innerText',
                'comment' => 'css://.comment@innerText',
            ],
            'repeated' => true,
        ],
    ],
    'export'              => [
        'type' => 'print',
    ],
];

# ====================

$spider = new Spider($config);

# ====================
# 回调钩子
# ====================

# ====================
# 插件加载
# ====================

$spider->use(User\Plugins\Buff::class);
$spider->use(User\Plugins\PrintOutExporter::class);

# ====================

$spider->start();
