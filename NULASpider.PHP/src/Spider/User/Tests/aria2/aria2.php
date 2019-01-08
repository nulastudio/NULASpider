<?php

use nulastudio\Networking\Rpc\Aria2;

$aria2 = new Aria2('http://localhost:6800/jsonrpc', 'secret', DIR_DATA);

// 添加url
// var_dump($aria2->addUri('https://cn.bing.com/az/hprichbg/rb/NLNorway_EN-CN8405509914_1920x1080.jpg'));
