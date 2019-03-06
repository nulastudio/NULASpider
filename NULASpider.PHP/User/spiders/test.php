<?php

use nulastudio\Log\FileLogger;
use nulastudio\Spider\Spider;

$config = [
    'thread'              => 5,
    'UI'                  => false,
    'logger'              => new FileLogger(DIR_LOG . '/' . date('Y-m-d') . '.log'),
    'scan_urls'           => [],
    'list_url_pattern'    => [],
    'content_url_pattern' => [],
    'fields'              => [],
    'export'              => [],
];

$spider = new Spider($config);

$spider->use(User\Plugins\Buff::class);

$spider->start();
