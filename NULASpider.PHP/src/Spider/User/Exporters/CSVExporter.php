<?php

namespace User\Exporters;

use nulastudio\Document\CSV\CSVHelper;
use nulastudio\Spider\Contracts\AbstructExporter;

class CSVExporter extends AbstructExporter
{
    private $fileName;
    private $hasHeader;
    private $csvHelper;

    public function __construct(array $config = [])
    {
        parent::__construct($config);
        if (!isset($config['file']) || !is_string($config['file'])) {
            throw new \Exception('config does not provide a valid file to export.');
        }
        $this->fileName  = $config['file'];
        $this->hasHeader = (bool) $config['header'] ?? true;
        $this->csvHelper = new CSVHelper($this->fileName);
    }
    public function export($data)
    {
        if ($this->hasHeader && !$this->csvHelper->hasData) {
            $this->csvHelper->writeRow(array_keys($data));
        }
        $this->csvHelper->writeRow($data);
    }
}
