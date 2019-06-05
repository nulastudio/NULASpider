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

$segments = nulastudio\Util\UriUtil::parseUrl('redis://:@127.0.0.1:8080/db0?charset=uft8&timeout=10&message=你好#aaa');
var_dump($segments);


$redisQueue = new nulastudio\Collections\RedisQueue(
    'redis://127.0.0.1/db0/testUrlQueue?password=test&prefix=pre_'
);

var_dump($redisQueue->exists('hello'));

$redisQueue->push('你好');

var_dump($redisQueue->exists('你好'));

$redisQueue->push('hello');

var_dump($redisQueue->count());

var_dump($redisQueue->peek());

var_dump($redisQueue->pop());

var_dump($redisQueue->count());

var_dump($redisQueue->exists('你好'));

var_dump($redisQueue->empty());

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
$spider = realpath(DIR_WORKING . '/' . ($argv[0] ?? ''));

if (!$spider || preg_match('#.*?\.php$#', $spider) !== 1) {
    echo "Please specify a valid startup spider script.\n";
    exit;
}

// try {
// 加载爬虫程序
// require __DIR__ . '/User/spiders/program.php';

// require $spider;
loadSingleScript($spider);
// } catch (\Exception $e) {
//     echo "Uncaught exception occured!\n";
//     echo $e;
//     exit;
// }
