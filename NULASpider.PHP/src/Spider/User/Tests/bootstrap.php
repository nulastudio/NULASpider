<?php

function pattern(string $pattern)
{
    return "./{$pattern}";
}

$patterns = [
    'ezSQL/PDO/pdo.php',
];

foreach ($patterns as $pattern) {
    require pattern($pattern);
}
