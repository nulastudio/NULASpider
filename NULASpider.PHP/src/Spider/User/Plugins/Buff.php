<?php

namespace User\Plugins;

use nulastudio\Spider\Contracts\PluginContract;

class Buff implements PluginContract
{
    public static function install($application, ...$params)
    {
        self::enable(...$params);
    }

    public static function enable($echo, ...$params)
    {
        $t = <<<'T'
                           _ooOoo_
                          o8888888o
                          88" . "88
                          (| -_- |)
                           O\ = /O
                       ____/`---'\____
                     .   ' \\| |// `.
                      / \\||| : |||// \
                    / _||||| -:- |||||- \
                      | | \\\ - /// | |
                    | \_| ''\---/'' | |
                     \ .-\__ `-` ___/-. /
                  ___`. .' /--.--\ `. . __
               ."" '< `.___\_<|>_/___.' >'"".
              | | : `- \`.;`\ _ /`;.`/ - ` : | |
                \ \ `-. \_ __\ /__ _/ .-` / /
        ======`-.____`-.___\_____/___.-`____.-'======
                           `=---='
        ^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^
                    佛祖保佑       永无BUG
T;
        if ($echo) {
            echo "{$t}\n";
        }
    }
}
