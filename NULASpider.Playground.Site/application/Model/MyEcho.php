<?php

class MyEcho
{
    public static function format(...$words)
    {
        echo implode(' ', $words);
    }
}
