<?php

use nulastudio\Log\FileLogger;
use nulastudio\Networking\Http\Header;
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
                'author' => [
                    'type'     => 'css',
                    'selector' => 'li:nth-child(1)>a',
                ],
                'time'   => [
                    'type'     => 'css',
                    'selector' => 'li:nth-child(2)>time',
                ],
                'author' => [
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
    '_export'              => [
        'type' => 'print',
    ],
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

// $package   = new ExcelPackage(__DIR__ . '/data/test.xlsx');
// $worksheet = $package->workBook->workSheets['sheet1'];

$spider->on_start = function ($spider) {
    Request::setDefaultHeader(
        Header::defaultHeader()->setHeaders([
            'User-Agent' => UserAgent::USER_AGENTS['WIN10_X64_EDGE'],
        ]));
};

// $spider->on_fetch_page = function ($spider, $data) use ($worksheet) {
//     var_dump($data);
//     // static $isInit = false;
//     // if (!$isInit) {
//     //     $worksheet->addRow(array_keys($data));
//     //     $isInit = true;
//     // }
//     // $worksheet->addRow($data);
// };

// $spider->on_exit = function ($spider, $exit_code) use ($package) {
//     // $package->save();
// };

// $spider->hooks['beforeStart'] = function ($spider) {
//     // $spider->logShow(LogLevel::DEBUG, 'beforeStart');
//     // echo 'beforeStart', PHP_EOL;
// };

// $spider->hooks['afterStart'] = function ($spider) {
//     // echo 'afterStart', PHP_EOL;
// };

// $spider->on_start = function ($spider) {
//     // echo 'on_start', PHP_EOL;
// };

$spider->use(User\Plugins\Buff::class);
$spider->use(User\Plugins\Pipeline::class);
$spider->use(User\Plugins\ExcelExporter::class);
$spider->use(User\Plugins\PrintOutExporter::class);
$spider->use(User\Plugins\PDOExporter::class);

$spider->start();
