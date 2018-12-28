<?php

namespace nulastudio\Networking\Rpc;

use liesauer\SimpleHttpClient;

class SimpleJsonRpcClient
{
    protected $url = '';

    public function __construct(string $url)
    {
        $this->url = $url;
    }

    public function __call(string $name, array $args)
    {
        return $this->call($name, ...$args);
    }

    public function call(string $name, ...$args)
    {
        function GUID()
        {
            $hash      = strtoupper(md5(uniqid(mt_rand(), true)));
            $seguments = [
                substr($hash, 0, 8),
                substr($hash, 8, 4),
                substr($hash, 12, 4),
                substr($hash, 16, 4),
                substr($hash, 20, 12),
            ];
            return implode('-', $seguments);
        }
        $json = json_encode([
            'jsonrpc' => '2.0',
            'id'      => GUID(),
            'method'  => $name,
            'params'  => $args,
        ]);
        $response = SimpleHttpClient::quickPost($this->url, [
            'Content-Type' => 'application/json; charset=utf-8',
        ], '', $json, [
            CURLOPT_TIMEOUT => 5,
        ]);
        return json_decode($response['data'], true);
    }
}
