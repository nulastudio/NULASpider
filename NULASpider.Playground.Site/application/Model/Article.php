<?php

class Article
{
    public $id;
    public $title;
    public $author;
    public $content;
    public $time;

    public function __construct($id, $title, $author, $content, $time = null)
    {
        $this->id = $id;
        $this->title = $title;
        $this->author = $author;
        $this->content = $content;
        $this->time = $time ?? time();
    }
    public static function generateActicleById($id)
    {
        $title = "这是第{$id}篇文章";
        $author = '50% & L!εsAμεr';
        $content = "这是第{$id}篇文章的内容鸭";
        return new static($id, $title, $author, $content);
    }
}