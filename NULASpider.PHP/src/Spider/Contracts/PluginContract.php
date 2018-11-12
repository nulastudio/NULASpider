<?php

namespace nulastudio\Spider\Contracts;

interface PluginContract
{
    public static function install($application, ...$params);
}
