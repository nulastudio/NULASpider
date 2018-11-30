<?php

namespace nulastudio\Networking\Rpc;

use nulastudio\Networking\Rpc\SimpleAria2JsonRpcClient;

class Aria2Downloader
{
    private $aria2;
    public $savePath;

    public function __construct(string $url, string $token = null, string $savePath = '')
    {
        $this->aria2 = new SimpleAria2JsonRpcClient($url, $token);
    }

    /**
     * 投递一个或多个下载链接至aria2，支持HTTP/HTTPS/FTP/SFTP/Magnet链接，当投递Magnet链接时，一次只能投递一个链接
     * @param  mixed  $url                                                       投递链接
     * @param  string $savePath                                                  保存位置，当以“/”结尾时，将当前文件保存到指定目录，否则保存到指定的文件名
     * @param  array  $options                                                   额外参数，参见aria2文档
     * @param  int    $position                                                  插入位置，当大于队列长度或未指定时插入到队列尾部
     * @return mixed  成功返回最新下载链接的GID，否则返回false
     */
    public function addUri($url, string $savePath = '', array $options = [], int $position = null)
    {
        if ($savePath) {
            $savePath = str_replace('\\', '/', $savePath);
            if (substr($savePath, -1) === '/') {
                $options['dir'] = substr($savePath, 0, -1);
            } else {
                $options['out'] = $savePath;
            }
        }
        $params = [
            is_array($url) ? $url : [$url],
            $options ?: '{}',
        ];
        if ($position !== null) {
            $params[] = $position;
        }
        $response = json_decode($this->aria2->addUri(...$params), true);
        return $response['result'] ?? false;
    }
}
