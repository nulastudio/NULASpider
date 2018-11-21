<?php

namespace User\Exporters;

use nulastudio\Document\EPPlus4PHP\ExcelPackage;
use nulastudio\Spider\Contracts\AbstructExporter;

class ExcelExporter extends AbstructExporter
{
    private $excelPackage;
    private $workSheet;
    private $fileName;
    private $sheetName;

    public function __construct(array $config = [])
    {
        if (!isset($config['file']) || !is_string($config['file'])) {
            throw new \Exception('config does not provide a valid file to export.');
        }
        if (!isset($config['sheet']) || !is_string($config['sheet'])) {
            throw new \Exception('config does not provide a valid sheet to export.');
        }
        $this->fileName     = $config['file'];
        $this->sheetName    = $config['sheet'];
        $this->excelPackage = new ExcelPackage($this->fileName);
        $this->workSheet    = $this->excelPackage->workBook->workSheets[$this->sheetName];
    }
    public function export($data)
    {
        if (!$this->workSheet->datas) {
            $this->workSheet->addRow(array_keys($data));
        } else {
            $this->workSheet->addRow($data);
        }
    }
    public function save()
    {
        $this->excelPackage->save();
    }
}
