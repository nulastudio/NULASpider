<?php

namespace User\Exporters;

use nulastudio\Spider\Contracts\ExporterContract;

class WriteToFileExporter implements ExporterContract
{
    private $file;

    public function __construct(array $config = [])
    {
        $this->file = $config['file'];
    }

    public function export($data)
    {
        file_put_contents($this->file, implode(', ', $data) . "\n", FILE_APPEND | LOCK_EX);
    }
}
