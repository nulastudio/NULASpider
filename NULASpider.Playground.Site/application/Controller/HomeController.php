<?php

class HomeController
{
    public function helloworld()
    {
        $format = MyEcho::format('Hello', 'World');
        return View::make('HelloWorld')->with('word', $format);
    }
}
