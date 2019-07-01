<?php defined('ACCESS') or die(); ?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=0">
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($article->title); ?></title>
    <style>
        .comment.level1 {
            margin-left: 0px;
        }
        .comment.level2 {
            margin-left: 20px;
        }
        .comment.level3 {
            margin-left: 40px;
        }
        .comment.level4 {
            margin-left: 60px;
        }
        .comment.level5 {
            margin-left: 80px;
        }
    </style>
</head>
<body>
    <article>
        <h1 id="article-title"><?php echo htmlspecialchars($article->title); ?></h1>
        <p id="article-author"><?php echo htmlspecialchars($article->author); ?></p>
        <p id="article-content"><?php echo htmlspecialchars($article->content); ?></p>
        <div class="comment level1">
            <p class="comment-author">评论者</p>
            <p class="comment-time">2019-06-30 16:27:37</p>
            <p class="comment-content">这是评论内容1</p>
            <div class="comment level2">
                <p class="comment-author">子评论者</p>
                <p class="comment-time">2019-06-30 16:27:37</p>
                <p class="comment-content">这是评论内容1的回复</p>
                <div class="comment level3">
                    <p class="comment-author">子子评论者</p>
                    <p class="comment-time">2019-06-30 16:27:37</p>
                    <p class="comment-content">这是评论内容1的回复的回复</p>
                    <div class="comment level3">
                        <p class="comment-author">子子子评论者</p>
                        <p class="comment-time">2019-06-30 16:27:37</p>
                        <p class="comment-content">这是评论内容1的回复的回复的回复</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="comment level1">
            <p class="comment-author">评论者</p>
            <p class="comment-time">2019-06-30 16:27:37</p>
            <p class="comment-content">这是评论内容2</p>
        </div>
        <div class="comment level1">
            <p class="comment-author">评论者</p>
            <p class="comment-time">2019-06-30 16:27:37</p>
            <p class="comment-content">这是评论内容3</p>
            <div class="comment level2">
                <p class="comment-author">子评论者1</p>
                <p class="comment-time">2019-06-30 16:27:37</p>
                <p class="comment-content">这是评论内容3的回复1</p>
            </div>
            <div class="comment level2">
                <p class="comment-author">子评论者2</p>
                <p class="comment-time">2019-06-30 16:27:37</p>
                <p class="comment-content">这是评论内容3的回复2</p>
            </div>
        </div>
        <div class="comment level1">
            <p class="comment-author">评论者</p>
            <p class="comment-time">2019-06-30 16:27:37</p>
            <p class="comment-content">这是评论内容4</p>
            <div class="comment level2">
                <p class="comment-author">子评论者1</p>
                <p class="comment-time">2019-06-30 16:27:37</p>
                <p class="comment-content">这是评论内容3的回复1</p>
                <div class="comment level3">
                    <p class="comment-author">子评论者1</p>
                    <p class="comment-time">2019-06-30 16:27:37</p>
                    <p class="comment-content">这是评论内容3的回复1</p>
                </div>
                <div class="comment level3">
                    <p class="comment-author">子评论者1</p>
                    <p class="comment-time">2019-06-30 16:27:37</p>
                    <p class="comment-content">这是评论内容3的回复122</p>
                </div>
            </div>
            <div class="comment level2">
                <p class="comment-author">子评论者2</p>
                <p class="comment-time">2019-06-30 16:27:37</p>
                <p class="comment-content">这是评论内容3的回复2</p>
            </div>
        </div>
        <div class="comment level1">
            <p class="comment-author">评论者</p>
            <p class="comment-time">2019-06-30 16:27:37</p>
            <p class="comment-content">这是评论内容5</p>
        </div>
    </article>
</body>
</html>