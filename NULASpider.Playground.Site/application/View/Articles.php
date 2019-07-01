<?php defined('ACCESS') or die(); ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta charset="UTF-8">
    <title>Page <?php echo (int) $currentPage; ?> of Articles</title>
</head>
<body>
    <?php
        foreach ($articles as $article) {
            echo <<<ARTICLE
<a href="/article/{$article->id}" target="_blank" rel="noopener noreferrer">{$article->title}</a><br />
ARTICLE;
        }
    ?>
    <div id="footer" style="margin-top: 20px; text-align: center">
    <?php
        foreach ($pages as $page) {
            echo <<<ARTICLE
<a href="/articles/{$page}" target="_self" rel="noopener noreferrer" style="margin: 0 3px">{$page}</a>
ARTICLE;
        }
    ?>
    </div>
</body>
</html>