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

    public function addUri(string $url, string $savePath = '', array $options = [])
    {
        if ($savePath) {
            $options['dir'] = $savePath;
        }
        $response = json_decode($this->aria2->addUri($url, $options ?: '{}'), true);
        return $response['result'] ?? false;
    }
}
