<?php

use nulastudio\Collections\ConcurrentMemoryQueue;
use nulastudio\Collections\ConcurrentUniqueQueue;
use nulastudio\Spider\Spider;

$config = [
    'thread'              => 5,
    'UI'                  => false,
    'urlQueue'            => new ConcurrentUniqueQueue(),
    'downloadQueue'       => new ConcurrentMemoryQueue(),
    'processQueue'        => new ConcurrentMemoryQueue(),
    'scan_urls'           => [
        'http://www.resgain.net/xmdq.html',
    ],
    'list_url_pattern'    => [
        function ($url) {
            return preg_match('/\w*\.resgain\.net\/name_list(_\w+)?\.html$/', $url) == 1;
        },
    ],
    'content_url_pattern' => [
        function ($url) {
            return preg_match('/\w*\.resgain\.net\/name_list(_\w+)?\.html$/', $url) == 1;
        },
    ],
    'fields'              => [
        'name' => [
            'type'     => 'xpath',
            'selector' => './/div[contains(@class,"namelist")]/div[1]/text()',
            'repeated' => true,
        ],
    ],
    // NOTE: 目前仅支持UTF-8编码的导出，而大部分的办公软件应该是不支持直接打开UTF-8编码的CSV文件的，会乱码，解决办法是自行转换编码或者使用导入功能（不直接打开）
    'export'              => [
        'type'   => 'csv',
        'file'   => DIR_DATA . '/names.csv',
        'header' => true,
    ],
];

$spider = new Spider($config);

// 导出前结构变换
$spider->on_fetch_page = function ($spider, $fields, $request, $response) {
    /**
     * 现在的结构是这样子的：
     * name: [
     *     name1,
     *     name2,
     *     name3,
     *     ...
     * ]
     *
     * 这样子的结构在表格中体现为一列多行，而这种结构在框架中是无法导出的，只能一行一行导出
     * 为了防止这种结构被编码（默认json_encode），我们需要将这种一列多行的结构转换成一行多列
     *
     * 现在的结构是这样子的：
     * 0: name1,
     * 1: name2,
     * 2: name3,
     * ...
     */
    return $fields['name'];
};

$spider->use(User\Plugins\CSVExporter::class);
$spider->use(User\Plugins\PrintOutExporter::class);

$spider->start();
