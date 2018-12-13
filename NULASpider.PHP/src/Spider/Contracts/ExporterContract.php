<?php

namespace nulastudio\Spider\Contracts;

interface ExporterContract
{
    public function __construct(array $config = []);
    public function export($data);
    public function handleUnsupportedData($data);
}
