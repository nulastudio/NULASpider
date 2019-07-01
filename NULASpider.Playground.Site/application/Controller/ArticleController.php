<?php

class ArticleController
{
    public function page($page = 1)
    {
        if ($page <= 0) {
            $page = 1;
        }
        if ($page > 50) {
            $page = 50;
        }

        // 生成文章列表
        $articles = [];
        for ($i = 1; $i <= 50; $i++) {
            $articles[] = Article::generateActicleById($i + ($page - 1) * 50);
        }

        // 生成分页
        $before = $page - 1;
        if ($before > 2) {
            $before = 2;
        }
        $after = 5 - 1 - $before;
        $pages = [$page];
        for ($i = 1; $i <= $before; $i++) {
            array_unshift($pages, $page - $i);
        }
        for ($i = 1; $i <= $after; $i++) {
            $newPage = $page + $i;
            if ($newPage > 50) {
                break;
            }
            array_push($pages, $newPage);
        }

        return View::make('Articles')
            ->with('articles', $articles)
            ->with('currentPage', $page)
            ->with('pages', $pages);
    }
    public function detail($id = 1) {
        if ($id <= 0) {
            $id = 1;
        }
        if ($id > 2500) {
            $id = 2500;
        }
        $article = Article::generateActicleById($id);

        return View::make('ArticleDetail')
            ->with('article', $article);
    }
}
