<?php

namespace User\Exporters;

use nulastudio\Spider\Contracts\AbstructExporter;

class JsonExporter extends AbstructExporter
{
    private $fileName;
    private $handle;
    private $fileSize  = -1;
    private $validJson = false;
    private $hasData   = false;
    private $endPos    = -1;
    private $endData;

    public function __construct(array $config = [])
    {
        if (!isset($config['file']) || !is_string($config['file'])) {
            throw new \Exception('config does not provide a valid file to export.');
        }
        $this->fileName = $config['file'];
        $this->handle   = fopen($this->fileName, 'a+');
        if (!$this->handle) {
            throw new \Exception("cannot open export file: {$this->fileName}.");
        }
        $this->prepare();
    }

    private function prepare()
    {
        // 移到文件尾
        if (fseek($this->handle, 0, SEEK_END) === -1) {
            // 移动失败
            // validJson不通过不写入任何数据
            return;
        }
        $pos = ftell($this->handle);
        if ($pos === false) {
            // 读取失败
            // validJson不通过不写入任何数据
            return;
        }
        $this->fileSize = $pos;
        /**
         * 为写入做准备
         *
         * 注意：不对非法json结构做检测
         *
         * 1.0 倒找 “]” 符号，最后一个非空白字符不为 “]” 视为无效 JSON
         * 1.1 若最后一个字符为 “]” ，再次正找 “[” 字符，若找不到视为无效 JSON
         * 1.2 若中间不存在非空白字符视为有数据，否则视为无数据
         * 1.3 没找到任何非空白字符视为空文件，在最后追加 “[]”
         *
         * 2. 在 “]” 符号前面开始追加数据
         *
         */
        $findEnd = false;
        for ($i = 1; $i <= $this->fileSize; $i++) {
            // 这里要传负数，被直觉坑了。。。
            $res = fseek($this->handle, -$i, SEEK_END);
            $char = fread($this->handle, 1);

            if ($char === ' ' || // space
                $char === "\t" ||    // tab
                $char === "\n" ||    // new line
                $char === "\r" ||    // carriage return
                $char === "\x0B" ||  // vertical tab
                $char === "\x0C"     // new page
            ) {
                // 跳过空白
                continue;
            }
            // unavailable
            // if (ctype_space($char)) {
            //     continue;
            // }

            if (!$findEnd) {
                if ($char === ']') {
                    $findEnd      = true;
                    $this->endPos = $this->fileSize - $i;
                } else {
                    // 最后不是 “]” ，非法 JSON 文件
                    // 不写入数据
                    $this->validJson = false;
                    return;
                }
            } else {
                if ($char === '[') {
                    // 数据头
                    /*
                     “[” 不用正找也可以，如果刚找到 “]” 代码就跑这里来了
                     证明 JSON 数组内的数据必然是错误结构的
                     我们也不要去管这种错误，因为这种错误只有人为修改才会发生的，概不负责
                     */
                } else {
                    // 拥有其他字符，视为存在数据
                    $this->hasData = true;
                }
                $this->validJson = true;
                break;
            }
        }
        if ($this->endPos === -1) {
            // 空文件
            // 往文件尾添加 “[]”，并将$endPos指向$this->fileSize + 1
            fseek($this->handle, 0, SEEK_END);
            fwrite($this->handle, '[]');
            $this->validJson = true;
            $this->endPos = $this->fileSize + 1;
        }
        fseek($this->handle, $this->endPos);
        $this->endData = fread($this->handle, $this->fileSize - $this->endPos);
    }
    public function export($data)
    {
        if (!$this->validJson) {
            return;
        }
        // 覆盖写入
        // 从endPos开始写数据，$hasData ? ',' : '' + $data + $this->endData
        fseek($this->handle, $this->endPos);
        $serializeJson = ($this->hasData ? ',' : '') . json_encode($data);
        $serializeJson .= $this->endData;
        $this->hasData = true;
        fwrite($this->handle, $serializeJson);

        // 重定向指针至endPos + strlen($data) - strlen($endData)
        // 因为每次都多写了 endData 的字节，所以要往回退
        $this->endPos += strlen($serializeJson) - strlen($this->endData);
        fseek($this->handle, $this->endPos);

        // flush
        fflush($this->handle);
    }
    public function close()
    {
        fflush($this->handle);
        fclose($this->handle);
    }
}
