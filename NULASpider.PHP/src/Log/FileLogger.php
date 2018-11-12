<?php

namespace nulastudio\Log;

use Psr\Log\AbstractLogger;

class FileLogger extends AbstractLogger
{
    protected $log_file;

    public function __construct($log_file)
    {
        $this->log_file = $log_file;
    }

    public function log($level, $message, array $context = [])
    {
        $time        = date('Y-m-d H:i:s');
        $upper_level = strtoupper($level);
        if (!is_string($message)) {
            $message = (string) $message;
        }
        $keys        = [];
        $replacement = [];
        foreach ($context as $key => $value) {
            $keys[]        = "{{$key}}";
            $replacement[] = $value;
        }
        $message     = str_replace($keys, $replacement, $message);
        $log_message = "[{$time}] {$upper_level}: {$message}";
        file_put_contents($this->log_file, "{$log_message}\n", FILE_APPEND | LOCK_EX);
    }
}
