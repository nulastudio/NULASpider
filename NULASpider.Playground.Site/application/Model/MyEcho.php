<?php

class MyEcho
{
    public static function format(...$words)
    {
        return implode(' ', $words);
    }
}
