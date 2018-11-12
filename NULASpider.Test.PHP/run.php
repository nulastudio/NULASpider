<?php

echo "Unit Tests Starting...\n";
$start_time = microtime(true);

$dir = __DIR__ . '/Tests';
require_once "{$dir}/ArrayUtilTest.php";
require_once "{$dir}/RpcClientTest.php";

$end_time  = microtime(true);
$took_time = $end_time - $start_time;
echo "Unit Tests Finished.\nTook {$took_time}s.\n";
