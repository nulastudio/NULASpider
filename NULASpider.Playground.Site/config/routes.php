<?php

use nulastudio\Middleware;

// 全局中间件
$middlewares = [
    function ($next, ...$params) {
        return $next(...$params);
    },
];

Router::get('/', 'HomeController@helloworld');

// 文章采集
Router::get('articles', 'ArticleController@page');
Router::get('articles/(:num)', 'ArticleController@page');
Router::get('article/(:num)', 'ArticleController@detail');

// 404处理
// Router::error(function(){});

// 模板渲染
Router::dispatch('View@process');

function middleware(callable $callback)
{
    global $middlewares;
    return (new Middleware)->send()->to($callback)->through($middlewares)->finish(function ($origin, $data) {
        // 响应封装器
        if (is_array($data)) {
            @header('Content-Type: application/json; charset=utf-8');
            echo json_encode($data);
        } else if (is_string($data)) {
            echo $data;
        }
    })->pack();
}
