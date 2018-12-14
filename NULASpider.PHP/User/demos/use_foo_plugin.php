<?php

use nulastudio\Spider\Spider;

// 配置
$config = [];

$spider = new Spider($config);

$spider->on_start = function ($spider) {
    $spider->foo('Hello', 'World');
};

$spider->use(User\Plugins\Foo::class);

$spider->run();
