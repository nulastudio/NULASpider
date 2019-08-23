<?php

require __DIR__ . '/vendor/autoload.php';

use nulastudio\Environment;

// 目录相关常量
// __DIR__ 关联当前目录
// .       关联启动目录
define('DIR_WORKING', Environment::getWorkingDirectory());
define('DIR_ROOT', Environment::getRootDirectory());
define('DIR_USER', DIR_ROOT . '/User');
define('DIR_PLUGINS', DIR_USER . '/Plugins');
define('DIR_EXPORTERS', DIR_USER . '/Exporters');
define('DIR_CONFIG', DIR_USER . '/config');
define('DIR_DATA', DIR_USER . '/data');
define('DIR_LOG', DIR_USER . '/log');
define('DIR_SPIDER', DIR_USER . '/spiders');
define('DIR_TMP', Environment::getTempDirectory());

define('TESTING_KEY', 'NULASPIDER_TESTING');

error_reporting(E_ALL);
// error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
date_default_timezone_set('Asia/Shanghai');

if (getenv(TESTING_KEY)) {
    require __DIR__ . '/src/Spider/User/Tests/bootstrap.php';
    return;
}

// foreach (glob(DIR_EXPORTERS . '/*.php') as $file) {
//     try {
//         loadSingleScript($file);
//     } catch (\Exception $e) {
//         echo "Uncaught exception occured while loading user's exporter: {$file}\n";
//         echo $e;
//         exit;
//     }
// }

// foreach (glob(DIR_PLUGINS . '/*.php') as $file) {
//     try {
//         loadSingleScript($file);
//     } catch (\Exception $e) {
//         echo "Uncaught exception occured while loading user's plugins: {$file}\n";
//         echo $e;
//         exit;
//     }
// }

/*
PHP的$argv的第一个参数是启动脚本，第二个开始才是参数
但在Peachpie中，第一个元素已经是参数了，启动脚本被移除
 */
$spider = $argv[0] ?? '';
if (!file_exists($spider)) {
    $spider = DIR_WORKING . '/' . $spider;
}

if (!$spider || preg_match('#.*?\.php$#', $spider) !== 1 && file_exists($spider)) {
    echo "Please specify a valid startup spider script.\n";
    exit;
}

// try {
    // 加载爬虫程序
    loadSingleScript($spider);
// } catch (\Exception $e) {
//     echo "Uncaught exception occured while booting spider!\n";
//     echo $e;
// }
