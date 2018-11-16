<?php

require __DIR__ . '/vendor/autoload.php';

use nulastudio\Environment;

// 目录相关常量
// __DIR__ 关联当前目录
// .       关联启动目录
define('DIR_ROOT', Environment::getRootDirectory());
define('DIR_USER', DIR_ROOT . '/User');
define('DIR_CONFIG', DIR_USER . '/config');
define('DIR_DATA', DIR_USER . '/data');
define('DIR_LOG', DIR_USER . '/log');
define('DIR_TMP', Environment::getTempDirectory());

error_reporting(E_ALL);
// error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
date_default_timezone_set('Asia/Shanghai');

// 加载爬虫程序
require __DIR__ . '/User/spiders/program.php';

// loadScript(realpath('./dynamicSpider.php'));
