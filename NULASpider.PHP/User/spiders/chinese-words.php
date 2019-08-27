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
        'http://xh.5156edu.com/pinyi.html',
    ],
    'list_url_pattern'    => [
        '#http://xh.5156edu.com/html2/\w+.html$#',
    ],
    'content_url_pattern' => [
        '#http://xh.5156edu.com/html2/\w+.html$#',
    ],
    'fields'              => [
        'pinyin' => 'xpath://.//font[@color="red"][1]/text()',
        'words'  => [
            'type'     => 'xpath',
            'selector' => './/table[3]/tr[1]/td[1]/table[2]/tr[position()>1]',
            'fields'   => [
                'pinyin2' => [
                    'type'     => 'xpath',
                    'selector' => './/td[1]/p[1]/text()',
                    'callback' => function ($pinyin2) {
                        return rtrim($pinyin2, ',');
                    },
                ],
                'words'   => [
                    'type'     => 'xpath',
                    'selector' => './/td[2]/a/text()',
                    'repeated' => true,
                    'callback' => function ($words) {
                        return preg_replace('#<span>\d+</span>#', '', $words);
                    },
                ],
            ],
            'repeated' => true,
        ],
    ],
    // NOTE: 目前仅支持UTF-8编码的导出，而大部分的办公软件应该是不支持直接打开UTF-8编码的CSV文件的，会乱码，解决办法是自行转换编码或者使用导入功能（不直接打开）
    'export'              => [
        'type'   => 'csv',
        'file'   => DIR_DATA . '/chinese-words.csv',
        'header' => true,
    ],
];

$spider = new Spider($config);

// 导出前结构变换
$spider->on_fetch_page = function ($spider, $fields, $request, $response) {
    /**
     * [
     *     'pinyin'=>string,
     *     'words'=>[
     *         [
     *             'pinyin2'=>string,
     *             'words'=>string[],
     *         ],
     *     ],
     * ]
     */
    /**
     * 复合数据转多行一维
     * [
     *     [
     *         '首字母'=>string,
     *         '拼音'=>string,
     *         '拼音2'=>string,
     *         '字'=>string,
     *     ],
     * ]
     */
    $data   = [];
    $index  = substr($fields['pinyin'], 0, 1);
    $pinyin = $fields['pinyin'];
    foreach ($fields['words'] as $words) {
        $pinyin2 = $words['pinyin2'];
        foreach ($words['words'] as $word) {
            $data[] = [
                '首字母' => $index,
                '拼音'  => $pinyin,
                '拼音2' => $pinyin2,
                '字'   => $word,
            ];
        }
    }

    /**
     * 变相地导出多行数据，但实际上不太推荐这样写，除非实在没办法组织导出数据结构了
     * 因为考虑到以后on_export的阻断性修改，这里的调用也可能会发生变化
     */
    $export = $spider->configs['export'];
    foreach ($data as $word) {
        // Spider中的回调函数外部是不让直接调用的，需要取出来再调用
        $on_export = $spider->on_export;
        $on_export($spider, $export, $word, $request, $response);
    }

    // 既然我们自己导出了，就返回false，让爬虫在后面直接跳过导出步骤
    return false;
};

$spider->use(User\Plugins\CSVExporter::class);
$spider->use(User\Plugins\PrintOutExporter::class);

$spider->start();
